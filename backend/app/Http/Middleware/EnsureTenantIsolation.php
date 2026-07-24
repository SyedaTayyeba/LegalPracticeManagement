<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs after `auth:api` on every tenant-scoped route.
 *
 * Platform Admins (firm_id null) are exempt — they operate across all firms.
 * Everyone else MUST have a firm_id, and any {firm} route-model-bound
 * parameter must match the authenticated user's own firm. This is the last
 * line of defense against tenant data leaking across firms even if a
 * controller forgets to scope a query.
 */
class EnsureTenantIsolation
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->isPlatformAdmin()) {
            return $next($request);
        }

        if (! $user->firm_id) {
            return response()->json(['message' => 'Account is not associated with a firm.'], 403);
        }

        $routeFirm = $request->route('firm');
        if ($routeFirm && (is_object($routeFirm) ? $routeFirm->id : $routeFirm) != $user->firm_id) {
            return response()->json(['message' => 'You do not have access to this firm workspace.'], 403);
        }

        return $next($request);
    }
}
