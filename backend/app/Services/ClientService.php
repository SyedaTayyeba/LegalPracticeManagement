<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientNote;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ClientService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function list(User $actor, array $filters): LengthAwarePaginator
    {
        $query = Client::query()->where('firm_id', $actor->firm_id);

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->orderBy('display_name')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function create(User $actor, array $data): Client
    {
        return DB::transaction(function () use ($actor, $data) {
            $client = Client::create([
                ...$data,
                'firm_id' => $actor->firm_id,
                'created_by' => $actor->id,
            ]);

            $this->auditLog->log('client.created', $actor, $client, [
                'display_name' => $client->display_name,
            ]);

            return $client;
        });
    }

    public function update(User $actor, Client $client, array $data): Client
    {
        $client->update($data);

        $this->auditLog->log('client.updated', $actor, $client, ['fields' => array_keys($data)]);

        return $client->fresh();
    }

    public function archive(User $actor, Client $client): void
    {
        $client->update(['status' => 'archived']);
        $client->delete(); // soft delete — preserves history for audit/compliance

        $this->auditLog->log('client.archived', $actor, $client);
    }

    public function addNote(User $actor, Client $client, array $data): ClientNote
    {
        $note = $client->notes()->create([
            'author_id' => $actor->id,
            'body' => $data['body'],
            'pinned' => $data['pinned'] ?? false,
        ]);

        $this->auditLog->log('client.note_added', $actor, $client, ['note_id' => $note->id]);

        return $note;
    }
}
