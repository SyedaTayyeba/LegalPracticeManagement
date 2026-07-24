<?php

namespace App\Services;

use App\Exceptions\ScheduleConflictException;
use App\Models\CaseFile;
use App\Models\CourtEvent;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CourtCalendarService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function list(User $actor, array $filters): LengthAwarePaginator
    {
        $query = CourtEvent::query()->where('firm_id', $actor->firm_id)
            ->with(['case:id,uuid,title', 'leadLawyer:id,uuid,name']);

        if ($actor->isClient()) {
            $query->whereHas('case.client', fn ($q) => $q->where('user_id', $actor->id));
        }

        if (! empty($filters['from'])) {
            $query->where('starts_at', '>=', Carbon::parse($filters['from']));
        }
        if (! empty($filters['to'])) {
            $query->where('starts_at', '<=', Carbon::parse($filters['to']));
        }
        if (! empty($filters['case_id'])) {
            $query->whereHas('case', fn ($q) => $q->where('uuid', $filters['case_id']));
        }
        if (! empty($filters['lawyer_id'])) {
            $query->whereHas('leadLawyer', fn ($q) => $q->where('uuid', $filters['lawyer_id']));
        }
        if (! empty($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        return $query->orderBy('starts_at')->paginate($filters['per_page'] ?? 50);
    }

    /**
     * Detects overlapping events for the same lawyer. Returns the conflicting
     * events (empty if none) rather than throwing, so the caller decides
     * whether to hard-block or just warn — matching the spec's "conflict
     * detection" requirement without forbidding legitimately overlapping
     * calendar entries (e.g. a status conference during a long deposition).
     */
    public function findConflicts(int $firmId, ?int $lawyerId, Carbon $startsAt, ?Carbon $endsAt, ?int $excludeEventId = null): \Illuminate\Support\Collection
    {
        if (! $lawyerId) {
            return collect();
        }

        $end = $endsAt ?? $startsAt->copy()->addHour();

        return CourtEvent::where('firm_id', $firmId)
            ->where('lead_lawyer_id', $lawyerId)
            ->when($excludeEventId, fn ($q) => $q->where('id', '!=', $excludeEventId))
            ->where('starts_at', '<', $end)
            ->where(function ($q) use ($startsAt) {
                $q->where('ends_at', '>', $startsAt)->orWhereNull('ends_at');
            })
            ->get();
    }

    public function create(User $actor, array $data): CourtEvent
    {
        return DB::transaction(function () use ($actor, $data) {
            $caseId = null;
            if (! empty($data['case_id'])) {
                $caseId = CaseFile::where('firm_id', $actor->firm_id)->where('uuid', $data['case_id'])->value('id');
            }

            $lawyerId = null;
            if (! empty($data['lead_lawyer_id'])) {
                $lawyerId = User::where('firm_id', $actor->firm_id)->where('uuid', $data['lead_lawyer_id'])->value('id');
            }

            $startsAt = Carbon::parse($data['starts_at']);
            $endsAt = isset($data['ends_at']) ? Carbon::parse($data['ends_at']) : null;

            $conflicts = $this->findConflicts($actor->firm_id, $lawyerId, $startsAt, $endsAt);

            if ($conflicts->isNotEmpty() && empty($data['force'])) {
                throw new ScheduleConflictException(
                    'This time conflicts with another event on the lawyer\'s calendar.',
                    $conflicts->pluck('title')->all()
                );
            }

            $event = CourtEvent::create([
                'firm_id' => $actor->firm_id,
                'case_id' => $caseId,
                'title' => $data['title'],
                'event_type' => $data['event_type'],
                'notes' => $data['notes'] ?? null,
                'location' => $data['location'] ?? null,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'lead_lawyer_id' => $lawyerId,
                'created_by' => $actor->id,
            ]);

            if ($lawyerId) {
                $event->attendees()->syncWithoutDetaching([$lawyerId]);
            }
            foreach ($data['attendee_ids'] ?? [] as $uuid) {
                $userId = User::where('firm_id', $actor->firm_id)->where('uuid', $uuid)->value('id');
                if ($userId) {
                    $event->attendees()->syncWithoutDetaching([$userId]);
                }
            }

            $this->auditLog->log('court_event.created', $actor, $event, ['title' => $event->title]);

            return $event->load(['case', 'leadLawyer', 'attendees']);
        });
    }

    public function update(User $actor, CourtEvent $event, array $data): CourtEvent
    {
        $startsAt = isset($data['starts_at']) ? Carbon::parse($data['starts_at']) : $event->starts_at;
        $endsAt = array_key_exists('ends_at', $data)
            ? ($data['ends_at'] ? Carbon::parse($data['ends_at']) : null)
            : $event->ends_at;

        if (isset($data['starts_at']) || array_key_exists('ends_at', $data)) {
            $conflicts = $this->findConflicts($event->firm_id, $event->lead_lawyer_id, $startsAt, $endsAt, $event->id);

            if ($conflicts->isNotEmpty() && empty($data['force'])) {
                throw new ScheduleConflictException(
                    'This time conflicts with another event on the lawyer\'s calendar.',
                    $conflicts->pluck('title')->all()
                );
            }
        }

        unset($data['force']);
        $event->update($data);

        $this->auditLog->log('court_event.updated', $actor, $event, ['fields' => array_keys($data)]);

        return $event->fresh(['case', 'leadLawyer', 'attendees']);
    }

    public function delete(User $actor, CourtEvent $event): void
    {
        $event->delete();
        $this->auditLog->log('court_event.deleted', $actor, $event);
    }
}
