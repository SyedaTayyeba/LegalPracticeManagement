<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentFolderRequest;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Resources\DocumentFolderResource;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Services\DocumentService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(private readonly DocumentService $documents) {}

    /** GET /api/v1/firm/documents?case_id=&client_id=&folder_id=&category=&search= */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Document::class);

        $filters = $request->only(['case_id', 'client_id', 'category', 'search', 'per_page']);
        if ($request->has('folder_id')) {
            $filters['folder_id'] = $request->query('folder_id') ?: null;
        }

        return DocumentResource::collection($this->documents->list($request->user(), $filters));
    }

    /** POST /api/v1/firm/documents (multipart/form-data) */
    public function store(StoreDocumentRequest $request)
    {
        $this->authorize('create', Document::class);

        $document = $this->documents->upload($request->user(), $request->file('file'), $request->validated());

        return response()->json([
            'message' => 'Document uploaded.',
            'document' => new DocumentResource($document),
        ], 201);
    }

    /** GET /api/v1/firm/documents/{document} */
    public function show(Document $document)
    {
        $this->authorize('view', $document);

        return new DocumentResource($document->load(['uploader', 'case', 'client', 'folder']));
    }

    /** GET /api/v1/firm/documents/{document}/download */
    public function download(Request $request, Document $document)
    {
        $this->authorize('view', $document);

        return $this->documents->download($request->user(), $document);
    }

    /** POST /api/v1/firm/documents/{document}/versions (multipart/form-data, field: file) */
    public function storeVersion(Request $request, Document $document)
    {
        $this->authorize('update', $document);
        $request->validate(['file' => ['required', 'file', 'max:51200']]);

        $newVersion = $this->documents->uploadNewVersion($request->user(), $document, $request->file('file'));

        return response()->json([
            'message' => "Version {$newVersion->version} uploaded.",
            'document' => new DocumentResource($newVersion),
        ], 201);
    }

    /** GET /api/v1/firm/documents/{document}/versions */
    public function versions(Document $document)
    {
        $this->authorize('view', $document);

        return DocumentResource::collection($this->documents->versionHistory($document));
    }

    /** DELETE /api/v1/firm/documents/{document} */
    public function destroy(Request $request, Document $document)
    {
        $this->authorize('delete', $document);

        $this->documents->delete($request->user(), $document);

        return response()->json(['message' => 'Document deleted.']);
    }

    /** GET /api/v1/firm/document-folders?case_id= */
    public function indexFolders(Request $request)
    {
        $folders = DocumentFolder::where('firm_id', $request->user()->firm_id)
            ->when($request->query('case_id'), fn ($q, $caseId) => $q->whereHas('case', fn ($c) => $c->where('uuid', $caseId)))
            ->with(['case', 'parent'])
            ->orderBy('name')
            ->get();

        return DocumentFolderResource::collection($folders);
    }

    /** POST /api/v1/firm/document-folders */
    public function storeFolder(StoreDocumentFolderRequest $request)
    {
        $folder = $this->documents->createFolder($request->user(), $request->validated());

        return response()->json([
            'message' => 'Folder created.',
            'folder' => new DocumentFolderResource($folder->load(['case', 'parent'])),
        ], 201);
    }
}
