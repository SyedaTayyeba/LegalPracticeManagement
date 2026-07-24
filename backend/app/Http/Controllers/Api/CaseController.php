<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignCaseTeamRequest;
use App\Http\Requests\StoreCaseNoteRequest;
use App\Http\Requests\StoreCaseRequest;
use App\Http\Requests\UpdateCaseRequest;
use App\Http\Requests\UpdateCaseStatusRequest;
use App\Http\Resources\CaseNoteResource;
use App\Http\Resources\CaseResource;
use App\Models\CaseFile;
use App\Models\User;
use App\Services\CaseService;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    public function __construct(private readonly CaseService $cases) {}

    /** GET /api/v1/firm/cases?search=&status=&client_id=&assigned_to_me=&per_page= */
    public function index(Request $request)
    {
        $this->authorize('viewAny', CaseFile::class);

        $cases = $this->cases->list(
            $request->user(),
            $request->only(['search', 'status', 'client_id', 'assigned_to_me', 'per_page'])
        );

        return CaseResource::collection($cases);
    }

    /** POST /api/v1/firm/cases */
    public function store(StoreCaseRequest $request)
    {
        $this->authorize('create', CaseFile::class);

        $case = $this->cases->create($request->user(), $request->validated());

        return response()->json([
            'message' => "Case {$case->case_number} opened.",
            'case' => new CaseResource($case),
        ], 201);
    }

    /** GET /api/v1/firm/cases/{case} — full workspace: notes + status history + team */
    public function show(Request $request, CaseFile $case)
    {
        $this->authorize('view', $case);

        return new CaseResource(
            $case->load(['client', 'leadLawyer', 'team', 'notes.author', 'statusHistory.changer'])
        );
    }

    /** PATCH /api/v1/firm/cases/{case} */
    public function update(UpdateCaseRequest $request, CaseFile $case)
    {
        $updated = $this->cases->update($request->user(), $case, $request->validated());

        return response()->json([
            'message' => 'Case updated.',
            'case' => new CaseResource($updated),
        ]);
    }

    /** PATCH /api/v1/firm/cases/{case}/status */
    public function updateStatus(UpdateCaseStatusRequest $request, CaseFile $case)
    {
        $updated = $this->cases->updateStatus(
            $request->user(),
            $case,
            $request->validated()['status'],
            $request->validated()['note'] ?? null
        );

        return response()->json([
            'message' => "Case status changed to {$updated->status}.",
            'case' => new CaseResource($updated->load('statusHistory.changer')),
        ]);
    }

    /** POST /api/v1/firm/cases/{case}/team */
    public function assignTeam(AssignCaseTeamRequest $request, CaseFile $case)
    {
        $data = $request->validated();
        $this->cases->assignTeamMember($request->user(), $case, $data['user_id'], $data['role_on_case']);

        return response()->json([
            'message' => 'Team member assigned.',
            'case' => new CaseResource($case->fresh()->load('team', 'leadLawyer')),
        ]);
    }

    /** DELETE /api/v1/firm/cases/{case}/team/{user} */
    public function removeTeamMember(Request $request, CaseFile $case, User $user)
    {
        $this->authorize('update', $case);

        $this->cases->removeTeamMember($request->user(), $case, $user->id);

        return response()->json([
            'message' => 'Team member removed.',
            'case' => new CaseResource($case->fresh()->load('team', 'leadLawyer')),
        ]);
    }

    /** POST /api/v1/firm/cases/{case}/notes */
    public function storeNote(StoreCaseNoteRequest $request, CaseFile $case)
    {
        $this->authorize('view', $case);

        $note = $this->cases->addNote($request->user(), $case, $request->validated());

        return response()->json([
            'message' => 'Note added.',
            'note' => new CaseNoteResource($note),
        ], 201);
    }

    /** DELETE /api/v1/firm/cases/{case} — archive (Firm Owner or lead lawyer only) */
    public function destroy(Request $request, CaseFile $case)
    {
        $this->authorize('delete', $case);

        $this->cases->archive($request->user(), $case);

        return response()->json(['message' => 'Case archived.']);
    }
}
