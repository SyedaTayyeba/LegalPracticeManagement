<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class RefreshTokenController extends Controller
{
    /**
     * Issues a new access token from a still-valid (not-yet-expired refresh
     * window) token, and blacklists the old one. The React client calls this
     * automatically from its axios response interceptor on a 401.
     */
    public function __invoke()
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();
        } catch (JWTException $e) {
            return response()->json(['message' => 'Session expired. Please log in again.'], 401);
        }

        return response()->json([
            'access_token' => $newToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ]);
    }
}
