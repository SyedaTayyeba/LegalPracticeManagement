<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConversationRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Services\CommunicationService;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(private readonly CommunicationService $comms) {}

    /** GET /api/v1/firm/conversations */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Conversation::class);

        return ConversationResource::collection($this->comms->list($request->user()));
    }

    /** POST /api/v1/firm/conversations — starts a thread with the first message */
    public function store(StoreConversationRequest $request)
    {
        $this->authorize('create', Conversation::class);

        $conversation = $this->comms->start($request->user(), $request->validated());

        return response()->json([
            'message' => 'Conversation started.',
            'conversation' => new ConversationResource($conversation),
        ], 201);
    }

    /** GET /api/v1/firm/conversations/{conversation} */
    public function show(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $this->comms->markRead($request->user(), $conversation);

        return new ConversationResource($conversation->load(['messages.sender', 'participants', 'case', 'client']));
    }

    /** POST /api/v1/firm/conversations/{conversation}/messages */
    public function storeMessage(StoreMessageRequest $request, Conversation $conversation)
    {
        $message = $this->comms->postMessage($request->user(), $conversation, $request->validated()['body']);

        return response()->json([
            'message' => 'Message sent.',
            'data' => new MessageResource($message->load('sender')),
        ], 201);
    }
}
