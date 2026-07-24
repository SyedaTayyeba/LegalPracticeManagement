<?php

namespace App\Services;

use App\Models\CaseFile;
use App\Models\Invoice;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * All reports are Firm-Owner-only (see ReportPolicy) — this is the analytics
 * layer described in Module 11 of the spec: active/completed cases, lawyer
 * workload, revenue, billing status, and case performance.
 */
class ReportService
{
    public function caseSummary(int $firmId): array
    {
        $counts = CaseFile::where('firm_id', $firmId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'by_status' => $counts,
            'active_total' => $counts->except(['completed', 'closed'])->sum(),
            'completed_total' => ($counts['completed'] ?? 0) + ($counts['closed'] ?? 0),
        ];
    }

    public function lawyerWorkload(int $firmId): array
    {
        return User::where('firm_id', $firmId)
            ->whereIn('role', ['lawyer', 'paralegal'])
            ->withCount([
                'leadCases as lead_case_count' => fn ($q) => $q->whereNotIn('status', ['completed', 'closed']),
            ])
            ->get(['id', 'uuid', 'name', 'role'])
            ->map(fn ($u) => [
                'id' => $u->uuid,
                'name' => $u->name,
                'role' => $u->role->value,
                'open_case_count' => $u->lead_case_count,
            ])->all();
    }

    public function revenueReport(int $firmId, ?string $from = null, ?string $to = null): array
    {
        $query = Invoice::where('firm_id', $firmId)->where('status', 'paid');

        if ($from) {
            $query->where('paid_on', '>=', $from);
        }
        if ($to) {
            $query->where('paid_on', '<=', $to);
        }

        $totalRevenue = (clone $query)->sum('total');

        $byMonth = (clone $query)
            ->selectRaw("DATE_FORMAT(paid_on, '%Y-%m') as month, SUM(total) as total")
            ->groupBy('month')->orderBy('month')->pluck('total', 'month');

        return [
            'total_revenue' => (float) $totalRevenue,
            'by_month' => $byMonth,
            'unbilled_hours_value' => $this->unbilledHoursValue($firmId),
        ];
    }

    private function unbilledHoursValue(int $firmId): float
    {
        return (float) TimeEntry::where('firm_id', $firmId)
            ->where('billable', true)
            ->whereNull('invoice_line_item_id')
            ->get()
            ->sum(fn ($entry) => $entry->amount());
    }

    public function billingStatus(int $firmId): array
    {
        $counts = Invoice::where('firm_id', $firmId)
            ->selectRaw('status, count(*) as total, sum(total) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return collect(Invoice::STATUSES)->mapWithKeys(fn ($status) => [
            $status => [
                'count' => (int) ($counts[$status]->total ?? 0),
                'amount' => (float) ($counts[$status]->amount ?? 0),
            ],
        ])->all();
    }

    public function casePerformance(int $firmId): array
    {
        $cases = CaseFile::where('firm_id', $firmId)->whereIn('status', ['completed', 'closed'])->get();

        $withDuration = $cases->filter(fn ($c) => $c->opened_on && $c->closed_on);

        $avgDays = $withDuration->isNotEmpty()
            ? $withDuration->avg(fn ($c) => $c->opened_on->diffInDays($c->closed_on))
            : null;

        return [
            'closed_case_count' => $cases->count(),
            'average_days_to_close' => $avgDays !== null ? round($avgDays, 1) : null,
        ];
    }
}
