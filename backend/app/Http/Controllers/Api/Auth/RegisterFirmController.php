<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterFirmRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;

class RegisterFirmController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function __invoke(RegisterFirmRequest $request)
    {
        $result = $this->authService->registerFirm($request->validated());

        return response()->json([
            'message' => 'Firm workspace created successfully.',
            'user' => new UserResource($result['user']),
            'access_token' => $result['token'],
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ], 201);
    }
}
