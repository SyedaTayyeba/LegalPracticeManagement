<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    /** GET /api/v1/firm/reports/cases */
    public function cases(Request $request)
    {
        Gate::authorize('viewReports');

        return response()->json(['data' => $this->reports->caseSummary($request->user()->firm_id)]);
    }

    /** GET /api/v1/firm/reports/workload */
    public function workload(Request $request)
    {
        Gate::authorize('viewReports');

        return response()->json(['data' => $this->reports->lawyerWorkload($request->user()->firm_id)]);
    }

    /** GET /api/v1/firm/reports/revenue?from=&to= */
    public function revenue(Request $request)
    {
        Gate::authorize('viewReports');

        $data = $this->reports->revenueReport($request->user()->firm_id, $request->query('from'), $request->query('to'));

        return response()->json(['data' => $data]);
    }

    /** GET /api/v1/firm/reports/billing-status */
    public function billingStatus(Request $request)
    {
        Gate::authorize('viewReports');

        return response()->json(['data' => $this->reports->billingStatus($request->user()->firm_id)]);
    }

    /** GET /api/v1/firm/reports/case-performance */
    public function casePerformance(Request $request)
    {
        Gate::authorize('viewReports');

        return response()->json(['data' => $this->reports->casePerformance($request->user()->firm_id)]);
    }
}
