<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AcceptInvitationRequest;
use App\Http\Requests\InviteUserRequest;
use App\Http\Resources\InvitationResource;
use App\Http\Resources\UserResource;
use App\Models\Invitation;
use App\Services\InvitationService;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(private readonly InvitationService $invitations) {}

    /** GET /api/v1/firm/invitations — list pending invitations for the current firm */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Invitation::class);

        $invitations = Invitation::where('firm_id', $request->user()->firm_id)
            ->with('inviter')
            ->latest()
            ->paginate(20);

        return InvitationResource::collection($invitations);
    }

    /** POST /api/v1/firm/invitations — Firm Owner invites a new team member or client */
    public function store(InviteUserRequest $request)
    {
        $invitation = $this->invitations->invite($request->user(), $request->validated());

        return response()->json([
            'message' => "Invitation sent to {$invitation->email}.",
            'invitation' => new InvitationResource($invitation),
        ], 201);
    }

    /** DELETE /api/v1/firm/invitations/{invitation} — revoke a pending invite */
    public function destroy(Request $request, Invitation $invitation)
    {
        $this->authorize('delete', $invitation);

        $invitation->delete();

        return response()->json(['message' => 'Invitation revoked.']);
    }

    /** POST /api/v1/invitations/accept — public endpoint, no auth required */
    public function accept(AcceptInvitationRequest $request)
    {
        $invitation = Invitation::where('token', $request->token)->firstOrFail();

        $result = $this->invitations->accept($invitation, $request->validated());

        return response()->json([
            'message' => 'Invitation accepted. Welcome to the team.',
            'user' => new UserResource($result['user']),
            'access_token' => $result['token'],
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ]);
    }
}
