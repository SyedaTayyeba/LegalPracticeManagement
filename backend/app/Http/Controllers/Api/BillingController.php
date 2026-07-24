<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\StoreTimeEntryRequest;
use App\Http\Resources\ExpenseResource;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\TimeEntryResource;
use App\Models\Invoice;
use App\Models\TimeEntry;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillingController extends Controller
{
    public function __construct(private readonly BillingService $billing) {}

    /*
    |--------------------------------------------------------------------
    | Time entries
    |--------------------------------------------------------------------
    */

    /** GET /api/v1/firm/time-entries?case_id=&unbilled= */
    public function indexTimeEntries(Request $request)
    {
        $this->authorize('viewAny', TimeEntry::class);

        return TimeEntryResource::collection(
            $this->billing->listTimeEntries($request->user(), $request->only(['case_id', 'unbilled', 'per_page']))
        );
    }

    /** POST /api/v1/firm/time-entries */
    public function storeTimeEntry(StoreTimeEntryRequest $request)
    {
        $this->authorize('create', TimeEntry::class);

        $entry = $this->billing->logTime($request->user(), $request->validated());

        return response()->json(['message' => 'Time logged.', 'time_entry' => new TimeEntryResource($entry)], 201);
    }

    /*
    |--------------------------------------------------------------------
    | Expenses
    |--------------------------------------------------------------------
    */

    /** POST /api/v1/firm/expenses */
    public function storeExpense(StoreExpenseRequest $request)
    {
        $expense = $this->billing->logExpense($request->user(), $request->validated());

        return response()->json(['message' => 'Expense logged.', 'expense' => new ExpenseResource($expense)], 201);
    }

    /*
    |--------------------------------------------------------------------
    | Invoices
    |--------------------------------------------------------------------
    */

    /** GET /api/v1/firm/invoices?status= */
    public function indexInvoices(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        return InvoiceResource::collection(
            $this->billing->listInvoices($request->user(), $request->only(['status', 'per_page']))
        );
    }

    /** POST /api/v1/firm/invoices — generate a draft invoice from time/expenses/manual items */
    public function storeInvoice(StoreInvoiceRequest $request)
    {
        $this->authorize('create', Invoice::class);

        $invoice = $this->billing->generateInvoice($request->user(), $request->validated());

        return response()->json([
            'message' => "Invoice {$invoice->invoice_number} created.",
            'invoice' => new InvoiceResource($invoice),
        ], 201);
    }

    /** GET /api/v1/firm/invoices/{invoice} */
    public function showInvoice(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        return new InvoiceResource($invoice->load(['lineItems', 'client', 'case']));
    }

    /** PATCH /api/v1/firm/invoices/{invoice}/status */
    public function updateInvoiceStatus(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $data = $request->validate(['status' => ['required', Rule::in(Invoice::STATUSES)]]);
        $updated = $this->billing->updateStatus($request->user(), $invoice, $data['status']);

        return response()->json(['message' => "Invoice marked {$updated->status}.", 'invoice' => new InvoiceResource($updated)]);
    }
}
