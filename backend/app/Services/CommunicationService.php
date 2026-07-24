<?php

namespace App\Services;

use App\Models\CaseFile;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CommunicationService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function list(User $actor): LengthAwarePaginator
    {
        $query = Conversation::query()->where('firm_id', $actor->firm_id)
            ->with(['case:id,uuid,title', 'client:id,uuid,display_name']);

        if (! $actor->isFirmOwner() && ! $actor->isPlatformAdmin()) {
            $query->whereHas('participants', fn ($q) => $q->where('users.id', $actor->id));
        }

        return $query->orderByDesc('last_message_at')->paginate(20);
    }

    public function start(User $actor, array $data): Conversation
    {
        return DB::transaction(function () use ($actor, $data) {
            $caseId = null;
            if (! empty($data['case_id'])) {
                $caseId = CaseFile::where('firm_id', $actor->firm_id)->where('uuid', $data['case_id'])->value('id');
            }

            $clientId = null;
            if (! empty($data['client_id'])) {
                $clientId = Client::where('firm_id', $actor->firm_id)->where('uuid', $data['client_id'])->value('id');
            } elseif ($actor->isClient()) {
                $clientId = Client::where('firm_id', $actor->firm_id)->where('user_id', $actor->id)->value('id');
            }

            $conversation = Conversation::create([
                'firm_id' => $actor->firm_id,
                'case_id' => $caseId,
                'client_id' => $clientId,
                'subject' => $data['subject'] ?? null,
            ]);

            $conversation->participants()->syncWithoutDetaching([$actor->id]);

            foreach ($data['participant_ids'] ?? [] as $uuid) {
                $userId = User::where('firm_id', $actor->firm_id)->where('uuid', $uuid)->value('id');
                if ($userId) {
                    $conversation->participants()->syncWithoutDetaching([$userId]);
                }
            }

            // Ensure the client's own portal user is always a participant if this
            // thread is tied to their client record.
            if ($clientId) {
                $portalUserId = Client::where('id', $clientId)->value('user_id');
                if ($portalUserId) {
                    $conversation->participants()->syncWithoutDetaching([$portalUserId]);
                }
            }

            $message = $this->postMessage($actor, $conversation, $data['body']);

            $this->auditLog->log('conversation.started', $actor, $conversation);

            return $conversation->load(['messages.sender', 'participants']);
        });
    }

    public function postMessage(User $actor, Conversation $conversation, string $body): Message
    {
        $message = $conversation->messages()->create([
            'sender_id' => $actor->id,
            'body' => $body,
        ]);

        $conversation->update(['last_message_at' => now()]);
        $conversation->participants()->updateExistingPivot($actor->id, ['last_read_at' => now()]);

        $this->auditLog->log('message.sent', $actor, $conversation, ['message_id' => $message->id]);

        $recipients = $conversation->participants()->where('users.id', '!=', $actor->id)->get();
        foreach ($recipients as $recipient) {
            $recipient->notify(new NewMessageNotification($message, $actor));
        }

        return $message;
    }

    public function markRead(User $actor, Conversation $conversation): void
    {
        $conversation->participants()->updateExistingPivot($actor->id, ['last_read_at' => now()]);
    }
}
