<?php

namespace App\Services;

use App\Models\CaseFile;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    /*
    |--------------------------------------------------------------------
    | Time tracking
    |--------------------------------------------------------------------
    */

    public function logTime(User $actor, array $data): TimeEntry
    {
        $caseId = CaseFile::where('firm_id', $actor->firm_id)->where('uuid', $data['case_id'])->value('id');

        $entry = TimeEntry::create([
            'firm_id' => $actor->firm_id,
            'case_id' => $caseId,
            'user_id' => $actor->id,
            'description' => $data['description'],
            'minutes' => $data['minutes'],
            'hourly_rate' => $data['hourly_rate'],
            'billable' => $data['billable'] ?? true,
            'entry_date' => $data['entry_date'],
        ]);

        $this->auditLog->log('time_entry.logged', $actor, $entry, ['minutes' => $entry->minutes]);

        return $entry->load(['case', 'user']);
    }

    public function listTimeEntries(User $actor, array $filters): LengthAwarePaginator
    {
        $query = TimeEntry::query()->where('firm_id', $actor->firm_id)->with(['case:id,uuid,title', 'user:id,uuid,name']);

        if (! $actor->isFirmOwner()) {
            $query->where('user_id', $actor->id);
        }
        if (! empty($filters['case_id'])) {
            $query->whereHas('case', fn ($q) => $q->where('uuid', $filters['case_id']));
        }
        if (isset($filters['unbilled'])) {
            $query->whereNull('invoice_line_item_id');
        }

        return $query->orderByDesc('entry_date')->paginate($filters['per_page'] ?? 20);
    }

    public function logExpense(User $actor, array $data): Expense
    {
        $caseId = null;
        if (! empty($data['case_id'])) {
            $caseId = CaseFile::where('firm_id', $actor->firm_id)->where('uuid', $data['case_id'])->value('id');
        }

        $expense = Expense::create([
            'firm_id' => $actor->firm_id,
            'case_id' => $caseId,
            'description' => $data['description'],
            'amount' => $data['amount'],
            'incurred_on' => $data['incurred_on'],
            'billable' => $data['billable'] ?? true,
            'created_by' => $actor->id,
        ]);

        $this->auditLog->log('expense.logged', $actor, $expense, ['amount' => $expense->amount]);

        return $expense->load('case');
    }

    /*
    |--------------------------------------------------------------------
    | Invoices
    |--------------------------------------------------------------------
    */

    public function listInvoices(User $actor, array $filters): LengthAwarePaginator
    {
        $query = Invoice::query()->where('firm_id', $actor->firm_id)->with(['client:id,uuid,display_name', 'case:id,uuid,title']);

        if ($actor->isClient()) {
            $client = Client::where('firm_id', $actor->firm_id)->where('user_id', $actor->id)->first();
            $query->where('client_id', $client?->id ?? 0);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('issue_date')->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Builds a draft invoice from selected unbilled time entries, unbilled
     * expenses, and/or free-form manual line items. Marking the pulled time
     * entries/expenses as invoiced happens atomically with invoice creation
     * so a time entry can never end up on two invoices.
     */
    public function generateInvoice(User $actor, array $data): Invoice
    {
        return DB::transaction(function () use ($actor, $data) {
            $client = Client::where('firm_id', $actor->firm_id)->where('uuid', $data['client_id'])->firstOrFail();

            $caseId = null;
            if (! empty($data['case_id'])) {
                $caseId = CaseFile::where('firm_id', $actor->firm_id)->where('uuid', $data['case_id'])->value('id');
            }

            $invoice = Invoice::create([
                'firm_id' => $actor->firm_id,
                'client_id' => $client->id,
                'case_id' => $caseId,
                'invoice_number' => $this->nextInvoiceNumber($actor->firm_id),
                'status' => 'draft',
                'issue_date' => now()->toDateString(),
                'due_date' => $data['due_date'],
                'tax_rate' => $data['tax_rate'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'created_by' => $actor->id,
            ]);

            foreach (TimeEntry::where('firm_id', $actor->firm_id)
                ->whereIn('uuid', $data['time_entry_ids'] ?? [])
                ->whereNull('invoice_line_item_id')
                ->get() as $entry) {
                $lineItem = $invoice->lineItems()->create([
                    'description' => "{$entry->description} ({$entry->minutes} min @ \${$entry->hourly_rate}/hr)",
                    'quantity' => round($entry->minutes / 60, 2),
                    'unit_price' => $entry->hourly_rate,
                ]);
                $entry->update(['invoice_line_item_id' => $lineItem->id]);
            }

            foreach (Expense::where('firm_id', $actor->firm_id)
                ->whereIn('uuid', $data['expense_ids'] ?? [])
                ->whereNull('invoice_line_item_id')
                ->get() as $expense) {
                $lineItem = $invoice->lineItems()->create([
                    'description' => $expense->description,
                    'quantity' => 1,
                    'unit_price' => $expense->amount,
                ]);
                $expense->update(['invoice_line_item_id' => $lineItem->id]);
            }

            foreach ($data['manual_line_items'] ?? [] as $manual) {
                $invoice->lineItems()->create([
                    'description' => $manual['description'],
                    'quantity' => $manual['quantity'],
                    'unit_price' => $manual['unit_price'],
                ]);
            }

            $invoice->recalculateTotals();

            $this->auditLog->log('invoice.generated', $actor, $invoice, ['invoice_number' => $invoice->invoice_number]);

            return $invoice->fresh(['lineItems', 'client', 'case']);
        });
    }

    private function nextInvoiceNumber(int $firmId): string
    {
        return DB::transaction(function () use ($firmId) {
            DB::table('firms')->where('id', $firmId)->lockForUpdate()->value('id');

            $year = now()->year;
            $count = Invoice::withTrashed()->where('firm_id', $firmId)
                ->where('invoice_number', 'like', "INV-{$year}-%")->count();

            return sprintf('INV-%d-%04d', $year, $count + 1);
        });
    }

    public function updateStatus(User $actor, Invoice $invoice, string $status): Invoice
    {
        $invoice->update([
            'status' => $status,
            'paid_on' => $status === 'paid' ? now()->toDateString() : $invoice->paid_on,
        ]);

        $this->auditLog->log('invoice.status_changed', $actor, $invoice, ['status' => $status]);

        return $invoice->fresh();
    }
}
