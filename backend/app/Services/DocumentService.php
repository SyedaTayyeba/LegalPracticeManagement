<?php

namespace App\Services;

use App\Models\CaseFile;
use App\Models\Client;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function list(User $actor, array $filters): LengthAwarePaginator
    {
        $query = Document::query()->where('firm_id', $actor->firm_id)->where('is_latest_version', true);

        if ($actor->isClient()) {
            $client = Client::where('firm_id', $actor->firm_id)->where('user_id', $actor->id)->first();
            $query->where('client_visible', true)
                ->where(function ($q) use ($client) {
                    $q->where('client_id', $client?->id ?? 0)
                        ->orWhereHas('case', fn ($c) => $c->where('client_id', $client?->id ?? 0));
                });
        }

        if (! empty($filters['case_id'])) {
            $query->whereHas('case', fn ($q) => $q->where('uuid', $filters['case_id']));
        }

        if (! empty($filters['client_id'])) {
            $query->whereHas('client', fn ($q) => $q->where('uuid', $filters['client_id']));
        }

        if (! empty($filters['folder_id'])) {
            $query->whereHas('folder', fn ($q) => $q->where('uuid', $filters['folder_id']));
        } elseif (array_key_exists('folder_id', $filters) && $filters['folder_id'] === null) {
            $query->whereNull('folder_id');
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query->with(['uploader:id,uuid,name', 'case:id,uuid,title', 'client:id,uuid,display_name'])
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function upload(User $actor, UploadedFile $file, array $data): Document
    {
        // File-type allow-list: legal document workspaces should never accept
        // executables or scripts, regardless of what the client-side accepts= hints.
        $allowedMimes = [
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/png', 'image/jpeg', 'image/tiff', 'text/plain',
        ];

        if (! in_array($file->getMimeType(), $allowedMimes, true)) {
            throw new \DomainException('This file type is not permitted for upload.');
        }

        return DB::transaction(function () use ($actor, $file, $data) {
            $caseId = null;
            if (! empty($data['case_id'])) {
                $caseId = CaseFile::where('firm_id', $actor->firm_id)->where('uuid', $data['case_id'])->value('id');
            }

            $clientId = null;
            if (! empty($data['client_id'])) {
                $clientId = Client::where('firm_id', $actor->firm_id)->where('uuid', $data['client_id'])->value('id');
            }

            $folderId = null;
            if (! empty($data['folder_id'])) {
                $folderId = DocumentFolder::where('firm_id', $actor->firm_id)->where('uuid', $data['folder_id'])->value('id');
            }

            // Private disk only — never `public`. Documents are served exclusively
            // through the authenticated, policy-checked download endpoint.
            $storedPath = $file->store("firms/{$actor->firm_id}/documents", 'local');

            $document = Document::create([
                'firm_id' => $actor->firm_id,
                'case_id' => $caseId,
                'client_id' => $clientId,
                'folder_id' => $folderId,
                'category' => $data['category'],
                'name' => $data['name'] ?? $file->getClientOriginalName(),
                'original_filename' => $file->getClientOriginalName(),
                'disk' => 'local',
                'path' => $storedPath,
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'version' => 1,
                'is_latest_version' => true,
                'client_visible' => $data['client_visible'] ?? false,
                'uploaded_by' => $actor->id,
            ]);

            $this->auditLog->log('document.uploaded', $actor, $document, [
                'name' => $document->name, 'size_bytes' => $document->size_bytes,
            ]);

            return $document->load(['uploader', 'case', 'client']);
        });
    }

    /**
     * Upload a new version of an existing document. The previous version is
     * kept on disk and in the DB (soft-deleted from the "latest" view via
     * is_latest_version=false) so version history is fully auditable.
     */
    public function uploadNewVersion(User $actor, Document $existing, UploadedFile $file): Document
    {
        return DB::transaction(function () use ($actor, $existing, $file) {
            $rootId = $existing->root_document_id ?? $existing->id;
            $nextVersion = Document::where(function ($q) use ($rootId) {
                $q->where('id', $rootId)->orWhere('root_document_id', $rootId);
            })->max('version') + 1;

            $storedPath = $file->store("firms/{$actor->firm_id}/documents", 'local');

            $newVersion = Document::create([
                'firm_id' => $existing->firm_id,
                'case_id' => $existing->case_id,
                'client_id' => $existing->client_id,
                'folder_id' => $existing->folder_id,
                'category' => $existing->category,
                'name' => $existing->name,
                'original_filename' => $file->getClientOriginalName(),
                'disk' => 'local',
                'path' => $storedPath,
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'root_document_id' => $rootId,
                'version' => $nextVersion,
                'is_latest_version' => true,
                'client_visible' => $existing->client_visible,
                'uploaded_by' => $actor->id,
            ]);

            Document::where('id', $existing->id)->update(['is_latest_version' => false]);
            if ($existing->root_document_id) {
                Document::where('id', $existing->root_document_id)->update(['is_latest_version' => false]);
            }

            $this->auditLog->log('document.version_uploaded', $actor, $newVersion, ['version' => $nextVersion]);

            return $newVersion->load(['uploader', 'case', 'client']);
        });
    }

    public function versionHistory(Document $document): \Illuminate\Support\Collection
    {
        $rootId = $document->root_document_id ?? $document->id;

        return Document::where('id', $rootId)
            ->orWhere('root_document_id', $rootId)
            ->with('uploader:id,uuid,name')
            ->orderByDesc('version')
            ->get();
    }

    /** Streams the file and records a download in the audit trail. */
    public function download(User $actor, Document $document)
    {
        $document->increment('download_count');

        $this->auditLog->log('document.downloaded', $actor, $document, ['version' => $document->version]);

        return Storage::disk($document->disk)->download($document->path, $document->original_filename);
    }

    public function createFolder(User $actor, array $data): DocumentFolder
    {
        $caseId = null;
        if (! empty($data['case_id'])) {
            $caseId = CaseFile::where('firm_id', $actor->firm_id)->where('uuid', $data['case_id'])->value('id');
        }

        $parentId = null;
        if (! empty($data['parent_folder_id'])) {
            $parentId = DocumentFolder::where('firm_id', $actor->firm_id)->where('uuid', $data['parent_folder_id'])->value('id');
        }

        $folder = DocumentFolder::create([
            'firm_id' => $actor->firm_id,
            'case_id' => $caseId,
            'parent_folder_id' => $parentId,
            'name' => $data['name'],
            'created_by' => $actor->id,
        ]);

        $this->auditLog->log('document_folder.created', $actor, $folder, ['name' => $folder->name]);

        return $folder;
    }

    public function delete(User $actor, Document $document): void
    {
        Storage::disk($document->disk)->delete($document->path);
        $document->delete();

        $this->auditLog->log('document.deleted', $actor, $document);
    }
}
