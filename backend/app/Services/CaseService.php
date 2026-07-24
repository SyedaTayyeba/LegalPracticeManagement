<?php

namespace App\Services;

use App\Models\CaseFile;
use App\Models\CaseNote;
use App\Models\Client;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CaseService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function list(User $actor, array $filters): LengthAwarePaginator
    {
        $query = CaseFile::query()->where('firm_id', $actor->firm_id)
            ->with(['client:id,uuid,display_name', 'leadLawyer:id,uuid,name']);

        // Portal clients only ever see cases tied to their own client record.
        if ($actor->isClient()) {
            $client = Client::where('firm_id', $actor->firm_id)->where('user_id', $actor->id)->first();
            $query->where('client_id', $client?->id ?? 0);
        }

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['client_id'])) {
            $query->whereHas('client', fn ($q) => $q->where('uuid', $filters['client_id']));
        }

        if (! empty($filters['assigned_to_me']) && $actor->isStaff()) {
            $query->where(function ($q) use ($actor) {
                $q->where('lead_lawyer_id', $actor->id)
                    ->orWhereHas('team', fn ($t) => $t->where('users.id', $actor->id));
            });
        }

        return $query->orderByDesc('created_at')->paginate($filters['per_page'] ?? 20);
    }

    public function create(User $actor, array $data): CaseFile
    {
        return DB::transaction(function () use ($actor, $data) {
            $client = Client::where('firm_id', $actor->firm_id)->where('uuid', $data['client_id'])->firstOrFail();

            $leadLawyerId = null;
            if (! empty($data['lead_lawyer_id'])) {
                $leadLawyerId = User::where('firm_id', $actor->firm_id)
                    ->where('uuid', $data['lead_lawyer_id'])->value('id');
            }

            $case = CaseFile::create([
                'firm_id' => $actor->firm_id,
                'case_number' => $this->nextCaseNumber($actor->firm_id),
                'title' => $data['title'],
                'case_type' => $data['case_type'],
                'client_id' => $client->id,
                'lead_lawyer_id' => $leadLawyerId,
                'opposing_party' => $data['opposing_party'] ?? null,
                'opposing_counsel' => $data['opposing_counsel'] ?? null,
                'court_name' => $data['court_name'] ?? null,
                'court_case_number' => $data['court_case_number'] ?? null,
                'court_jurisdiction' => $data['court_jurisdiction'] ?? null,
                'opened_on' => $data['opened_on'] ?? now()->toDateString(),
                'filed_on' => $data['filed_on'] ?? null,
                'description' => $data['description'] ?? null,
                'created_by' => $actor->id,
            ]);

            $case->statusHistory()->create([
                'changed_by' => $actor->id,
                'from_status' => null,
                'to_status' => 'new',
                'note' => 'Case opened.',
            ]);

            if ($leadLawyerId) {
                $case->team()->syncWithoutDetaching([$leadLawyerId => ['role_on_case' => 'lead']]);
            }

            foreach ($data['team_user_ids'] ?? [] as $uuid) {
                $userId = User::where('firm_id', $actor->firm_id)->where('uuid', $uuid)->value('id');
                if ($userId && $userId !== $leadLawyerId) {
                    $case->team()->syncWithoutDetaching([$userId => ['role_on_case' => 'support']]);
                }
            }

            $this->auditLog->log('case.created', $actor, $case, ['case_number' => $case->case_number]);

            return $case->load(['client', 'leadLawyer', 'team']);
        });
    }

    /**
     * Firm-scoped, year-scoped sequential case number, e.g. "2026-0143".
     * Locks the firm row to serialize concurrent case creation and avoid
     * duplicate numbers under load.
     */
    private function nextCaseNumber(int $firmId): string
    {
        return DB::transaction(function () use ($firmId) {
            DB::table('firms')->where('id', $firmId)->lockForUpdate()->value('id');

            $year = now()->year;
            $count = CaseFile::withTrashed()
                ->where('firm_id', $firmId)
                ->where('case_number', 'like', "{$year}-%")
                ->count();

            return sprintf('%d-%04d', $year, $count + 1);
        });
    }

    public function update(User $actor, CaseFile $case, array $data): CaseFile
    {
        if (array_key_exists('lead_lawyer_id', $data)) {
            $leadLawyerId = $data['lead_lawyer_id']
                ? User::where('firm_id', $actor->firm_id)->where('uuid', $data['lead_lawyer_id'])->value('id')
                : null;
            $data['lead_lawyer_id'] = $leadLawyerId;

            if ($leadLawyerId) {
                $case->team()->syncWithoutDetaching([$leadLawyerId => ['role_on_case' => 'lead']]);
            }
        }

        $case->update($data);

        $this->auditLog->log('case.updated', $actor, $case, ['fields' => array_keys($data)]);

        return $case->fresh(['client', 'leadLawyer', 'team']);
    }

    public function updateStatus(User $actor, CaseFile $case, string $status, ?string $note = null): CaseFile
    {
        $from = $case->status;

        $case->update([
            'status' => $status,
            'closed_on' => in_array($status, ['completed', 'closed'], true) ? now()->toDateString() : $case->closed_on,
        ]);

        $case->statusHistory()->create([
            'changed_by' => $actor->id,
            'from_status' => $from,
            'to_status' => $status,
            'note' => $note,
        ]);

        $this->auditLog->log('case.status_changed', $actor, $case, ['from' => $from, 'to' => $status]);

        return $case->fresh();
    }

    public function assignTeamMember(User $actor, CaseFile $case, string $userUuid, string $roleOnCase): void
    {
        $userId = User::where('firm_id', $actor->firm_id)->where('uuid', $userUuid)->value('id');

        $case->team()->syncWithoutDetaching([$userId => ['role_on_case' => $roleOnCase]]);

        if ($roleOnCase === 'lead') {
            $case->update(['lead_lawyer_id' => $userId]);
        }

        $this->auditLog->log('case.team_assigned', $actor, $case, ['user_id' => $userId, 'role' => $roleOnCase]);
    }

    public function removeTeamMember(User $actor, CaseFile $case, int $userId): void
    {
        $case->team()->detach($userId);

        if ($case->lead_lawyer_id === $userId) {
            $case->update(['lead_lawyer_id' => null]);
        }

        $this->auditLog->log('case.team_removed', $actor, $case, ['user_id' => $userId]);
    }

    public function addNote(User $actor, CaseFile $case, array $data): CaseNote
    {
        $note = $case->notes()->create([
            'author_id' => $actor->id,
            'body' => $data['body'],
            'pinned' => $data['pinned'] ?? false,
            'client_visible' => $data['client_visible'] ?? false,
        ]);

        $this->auditLog->log('case.note_added', $actor, $case, ['note_id' => $note->id]);

        return $note;
    }

    public function archive(User $actor, CaseFile $case): void
    {
        $case->delete();
        $this->auditLog->log('case.archived', $actor, $case);
    }
}
