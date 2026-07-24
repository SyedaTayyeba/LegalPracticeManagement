<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourtEventRequest;
use App\Http\Requests\UpdateCourtEventRequest;
use App\Http\Resources\CourtEventResource;
use App\Models\CourtEvent;
use App\Services\CourtCalendarService;
use Illuminate\Http\Request;

class CourtEventController extends Controller
{
    public function __construct(private readonly CourtCalendarService $calendar) {}

    /** GET /api/v1/firm/calendar?from=&to=&case_id=&lawyer_id=&event_type= */
    public function index(Request $request)
    {
        $this->authorize('viewAny', CourtEvent::class);

        return CourtEventResource::collection(
            $this->calendar->list($request->user(), $request->only([
                'from', 'to', 'case_id', 'lawyer_id', 'event_type', 'per_page',
            ]))
        );
    }

    /** POST /api/v1/firm/calendar — returns 409 with conflicting_events if the lawyer is double-booked */
    public function store(StoreCourtEventRequest $request)
    {
        $this->authorize('create', CourtEvent::class);

        $event = $this->calendar->create($request->user(), $request->validated());

        return response()->json(['message' => 'Event scheduled.', 'event' => new CourtEventResource($event)], 201);
    }

    /** GET /api/v1/firm/calendar/{event} */
    public function show(CourtEvent $event)
    {
        $this->authorize('view', $event);

        return new CourtEventResource($event->load(['case', 'leadLawyer', 'attendees']));
    }

    /** PATCH /api/v1/firm/calendar/{event} */
    public function update(UpdateCourtEventRequest $request, CourtEvent $event)
    {
        $updated = $this->calendar->update($request->user(), $event, $request->validated());

        return response()->json(['message' => 'Event updated.', 'event' => new CourtEventResource($updated)]);
    }

    /** DELETE /api/v1/firm/calendar/{event} */
    public function destroy(Request $request, CourtEvent $event)
    {
        $this->authorize('delete', $event);

        $this->calendar->delete($request->user(), $event);

        return response()->json(['message' => 'Event removed from calendar.']);
    }
}
