<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientNoteRequest;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientNoteResource;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(private readonly ClientService $clients) {}

    /** GET /api/v1/firm/clients?search=&status=&type=&per_page= */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $clients = $this->clients->list($request->user(), $request->only(['search', 'status', 'type', 'per_page']));

        return ClientResource::collection($clients);
    }

    /** POST /api/v1/firm/clients */
    public function store(StoreClientRequest $request)
    {
        $this->authorize('create', Client::class);

        $client = $this->clients->create($request->user(), $request->validated());

        return response()->json([
            'message' => 'Client created.',
            'client' => new ClientResource($client),
        ], 201);
    }

    /** GET /api/v1/firm/clients/{client} — includes notes timeline */
    public function show(Request $request, Client $client)
    {
        $this->authorize('view', $client);

        return new ClientResource($client->load(['creator', 'notes.author']));
    }

    /** PATCH /api/v1/firm/clients/{client} */
    public function update(UpdateClientRequest $request, Client $client)
    {
        $updated = $this->clients->update($request->user(), $client, $request->validated());

        return response()->json([
            'message' => 'Client updated.',
            'client' => new ClientResource($updated),
        ]);
    }

    /** DELETE /api/v1/firm/clients/{client} — soft delete / archive (Firm Owner only) */
    public function destroy(Request $request, Client $client)
    {
        $this->authorize('delete', $client);

        $this->clients->archive($request->user(), $client);

        return response()->json(['message' => 'Client archived.']);
    }

    /** POST /api/v1/firm/clients/{client}/notes */
    public function storeNote(StoreClientNoteRequest $request, Client $client)
    {
        $this->authorize('view', $client);

        $note = $this->clients->addNote($request->user(), $client, $request->validated());

        return response()->json([
            'message' => 'Note added.',
            'note' => new ClientNoteResource($note),
        ], 201);
    }
}
