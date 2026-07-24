<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptions) {}

    /** GET /api/v1/plans — public, powers the pricing page */
    public function index()
    {
        return SubscriptionPlanResource::collection(
            SubscriptionPlan::where('is_active', true)->get()
        );
    }

    /** PATCH /api/v1/firm/plan — Firm Owner changes their subscription plan */
    public function changePlan(Request $request)
    {
        $this->authorize('update', $request->user()->firm);

        $data = $request->validate([
            'plan_key' => ['required', Rule::exists('subscription_plans', 'key')->where('is_active', true)],
        ]);

        $firm = $this->subscriptions->changePlan($request->user(), $data['plan_key']);

        return response()->json([
            'message' => "Plan changed to {$firm->plan}.",
            'firm' => new \App\Http\Resources\FirmResource($firm),
        ]);
    }
}
