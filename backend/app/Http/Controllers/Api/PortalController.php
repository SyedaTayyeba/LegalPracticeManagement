<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CourtEvent;
use App\Models\Document;
use App\Models\Invoice;
use Illuminate\Http\Request;

/**
 * Aggregates data the Client Portal dashboard needs into a single call,
 * scoped entirely to the authenticated client's own records. Every
 * underlying query reuses the same tenant + client-ownership scoping already
 * enforced by CasePolicy / DocumentPolicy / InvoicePolicy elsewhere in the
 * API — this controller does not bypass those rules, it just aggregates.
 */
class PortalController extends Controller
{
    /** GET /api/v1/firm/portal/dashboard */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isClient(), 403, 'This endpoint is for client portal accounts only.');

        $client = Client::where('firm_id', $user->firm_id)->where('user_id', $user->id)->first();

        if (! $client) {
            return response()->json([
                'message' => 'No client record is linked to this account yet.',
                'data' => null,
            ]);
        }

        $caseIds = $client->cases()->pluck('id');

        return response()->json(['data' => [
            'client_name' => $client->display_name,
            'open_case_count' => $client->cases()->whereNotIn('status', ['completed', 'closed'])->count(),
            'total_case_count' => $client->cases()->count(),
            'upcoming_events' => CourtEvent::whereIn('case_id', $caseIds)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(5)
                ->get(['uuid', 'title', 'starts_at', 'event_type'])
                ->map(fn ($e) => [
                    'id' => $e->uuid, 'title' => $e->title,
                    'starts_at' => $e->starts_at->toIso8601String(), 'event_type' => $e->event_type,
                ]),
            'recent_documents' => Document::where('client_visible', true)
                ->where(fn ($q) => $q->where('client_id', $client->id)->orWhereIn('case_id', $caseIds))
                ->where('is_latest_version', true)
                ->orderByDesc('created_at')->limit(5)
                ->get(['uuid', 'name', 'category', 'created_at'])
                ->map(fn ($d) => ['id' => $d->uuid, 'name' => $d->name, 'category' => $d->category, 'created_at' => $d->created_at->toIso8601String()]),
            'unread_conversations' => \Illuminate\Support\Facades\DB::table('conversation_participants')
                ->join('conversations', 'conversations.id', '=', 'conversation_participants.conversation_id')
                ->where('conversation_participants.user_id', $user->id)
                ->where('conversations.firm_id', $user->firm_id)
                ->where(function ($q) {
                    $q->whereNull('conversation_participants.last_read_at')
                        ->orWhereColumn('conversations.last_message_at', '>', 'conversation_participants.last_read_at');
                })
                ->count(),
            'outstanding_invoice_total' => (float) Invoice::where('client_id', $client->id)
                ->whereIn('status', ['sent', 'overdue'])->sum('total'),
        ]]);
    }
}
