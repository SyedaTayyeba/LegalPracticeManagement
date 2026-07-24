<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiActivity
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            && $response->getStatusCode() < 400
            && $request->user()
        ) {
            $this->auditLog->log(
                action: $request->route()?->getName() ?? $request->path(),
                actor: $request->user(),
                metadata: ['method' => $request->method(), 'path' => $request->path()],
                request: $request,
            );
        }

        return $response;
    }
}
