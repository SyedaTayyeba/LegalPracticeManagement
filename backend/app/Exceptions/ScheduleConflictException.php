<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleConflictException extends Exception
{
    public function __construct(string $message, private readonly array $conflictingEvents = [])
    {
        parent::__construct($message);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'error_code' => 'SCHEDULE_CONFLICT',
            'conflicting_events' => $this->conflictingEvents,
        ], 409);
    }
}
