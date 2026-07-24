<?php

namespace App\Services;

use App\Events\UserInvited;
use App\Exceptions\SeatLimitReachedException;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class InvitationService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function invite(User $inviter, array $data): Invitation
    {
        $firm = $inviter->firm;

        if (in_array($data['role'], ['firm_owner', 'lawyer', 'paralegal'], true) && $firm->hasReachedSeatLimit()) {
            throw new SeatLimitReachedException(
                "Firm '{$firm->name}' has reached its seat limit of {$firm->seat_limit} for the {$firm->plan} plan."
            );
        }

        $invitation = Invitation::create([
            'firm_id' => $firm->id,
            'invited_by' => $inviter->id,
            'email' => $data['email'],
            'role' => $data['role'],
        ]);

        $this->auditLog->log('user.invited', $inviter, $invitation, ['role' => $data['role']]);

        event(new UserInvited($invitation));

        return $invitation;
    }

    /**
     * Accept an invitation: creates the User record inside the inviting
     * firm's tenant and immediately signs them in with a JWT.
     */
    public function accept(Invitation $invitation, array $data): array
    {
        if ($invitation->isExpired()) {
            throw new \DomainException('This invitation has expired. Ask your firm admin to resend it.');
        }

        if ($invitation->isAccepted()) {
            throw new \DomainException('This invitation has already been used.');
        }

        return DB::transaction(function () use ($invitation, $data) {
            $user = User::create([
                'firm_id' => $invitation->firm_id,
                'name' => $data['name'],
                'email' => $invitation->email,
                'password' => Hash::make($data['password']),
                'role' => $invitation->role,
                'status' => 'active',
                'email_verified_at' => now(), // invitation email itself is proof of ownership
            ]);

            $invitation->update([
                'accepted_at' => now(),
                'accepted_by' => $user->id,
            ]);

            $this->auditLog->log('invitation.accepted', $user, $invitation);

            $token = JWTAuth::fromUser($user);

            return ['user' => $user->load('firm'), 'token' => $token];
        });
    }
}
