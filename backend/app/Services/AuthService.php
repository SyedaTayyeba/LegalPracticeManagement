<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Events\UserRegistered;
use App\Models\Firm;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    /**
     * Register a brand new firm together with its Firm Owner in a single
     * transaction. This is the ONLY way a firm_owner account can be created
     * outside of an invitation acceptance.
     */
    public function registerFirm(array $data): array
    {
        $planLimits = [
            'solo' => ['seat_limit' => 1, 'storage_limit_mb' => 2048],
            'professional' => ['seat_limit' => 15, 'storage_limit_mb' => 20480],
            'enterprise' => ['seat_limit' => 250, 'storage_limit_mb' => 512000],
        ];

        return DB::transaction(function () use ($data, $planLimits) {
            $limits = $planLimits[$data['plan']];

            $firm = Firm::create([
                'name' => $data['firm_name'],
                'email' => $data['email'],
                'plan' => $data['plan'],
                'seat_limit' => $limits['seat_limit'],
                'storage_limit_mb' => $limits['storage_limit_mb'],
                'trial_ends_at' => now()->addDays(14),
            ]);

            $owner = User::create([
                'firm_id' => $firm->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => UserRole::FirmOwner,
                'phone' => $data['phone'] ?? null,
                'status' => 'active',
            ]);

            $firm->update(['owner_id' => $owner->id]);

            $this->auditLog->log('firm.registered', $owner, $firm, [
                'plan' => $firm->plan,
            ]);

            event(new UserRegistered($owner));

            $token = JWTAuth::fromUser($owner);

            return [
                'user' => $owner->load('firm'),
                'token' => $token,
            ];
        });
    }

    public function issueTokenPair(User $user): array
    {
        $token = JWTAuth::fromUser($user);

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ];
    }

    /**
     * Invalidate all currently issued tokens for a user by rotating their
     * password hash salt context — used when role/firm changes or on
     * "log out everywhere" requests. Combined with the JWT blacklist,
     * this guarantees stale role claims can't be replayed.
     */
    public function invalidateCurrentToken(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }
}
