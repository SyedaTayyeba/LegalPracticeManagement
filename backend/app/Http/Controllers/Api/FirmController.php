<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FirmResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FirmController extends Controller
{
    /** GET /api/v1/firm — current tenant's workspace details */
    public function show(Request $request)
    {
        $this->authorize('view', $request->user()->firm);

        return new FirmResource($request->user()->firm);
    }

    /** PATCH /api/v1/firm — update firm settings (Firm Owner only) */
    public function update(Request $request)
    {
        $firm = $request->user()->firm;
        $this->authorize('update', $firm);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:120'],
        ]);

        $firm->update($data);

        return new FirmResource($firm->fresh());
    }

    /** GET /api/v1/firm/team — list all staff + clients in the tenant */
    public function team(Request $request)
    {
        $this->authorize('view', $request->user()->firm);

        $users = $request->user()->firm->users()
            ->when($request->query('role'), fn ($q, $role) => $q->where('role', $role))
            ->when($request->query('search'), function ($q, $search) {
                $q->where(fn ($q2) => $q2->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->paginate(20);

        return UserResource::collection($users);
    }

    /** PATCH /api/v1/firm/team/{user}/suspend — Firm Owner deactivates a staff/client account */
    public function suspendMember(Request $request, \App\Models\User $user)
    {
        $this->authorize('update', $user);

        $data = $request->validate(['status' => [Rule::in(['active', 'suspended'])]]);
        $user->update(['status' => $data['status']]);

        return new UserResource($user);
    }
}
