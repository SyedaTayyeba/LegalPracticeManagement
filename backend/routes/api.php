<?php

use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RefreshTokenController;
use App\Http\Controllers\Api\Auth\RegisterFirmController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\CaseController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\CourtEventController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\PortalController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\FirmController;
use App\Http\Controllers\Api\InvitationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — v1
|--------------------------------------------------------------------------
| Every route here is prefixed with /api/v1. Public (unauthenticated)
| routes are rate-limited harder than authenticated ones to blunt
| credential-stuffing / brute force attempts.
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------
    | Public auth routes
    |--------------------------------------------------------------------
    */
    Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
        Route::post('register', RegisterFirmController::class)->name('auth.register');
        Route::post('login', LoginController::class)->name('auth.login');
        Route::post('forgot-password', ForgotPasswordController::class)->name('auth.forgot-password');
        Route::post('reset-password', ResetPasswordController::class)->name('auth.reset-password');
    });

    Route::post('invitations/accept', [InvitationController::class, 'accept'])
        ->middleware('throttle:10,1')
        ->name('invitations.accept');

    // Public: pricing page reads available plans without auth
    Route::get('plans', [PlanController::class, 'index'])->name('plans.index');

    /*
    |--------------------------------------------------------------------
    | Authenticated routes (JWT required)
    |--------------------------------------------------------------------
    */
    Route::middleware('auth:api')->group(function () {

        Route::prefix('auth')->group(function () {
            Route::post('logout', LogoutController::class)->name('auth.logout');
            Route::post('refresh', RefreshTokenController::class)->name('auth.refresh');
            Route::get('me', MeController::class)->name('auth.me');
            Route::post('email/verify', [EmailVerificationController::class, 'verify'])->name('verification.verify');
            Route::post('email/resend', [EmailVerificationController::class, 'resend'])->name('verification.resend');
        });

        /*
        |----------------------------------------------------------------
        | Tenant-scoped firm workspace routes
        |----------------------------------------------------------------
        | `tenant` middleware guarantees the authenticated user can only
        | ever touch their own firm's data. `audit` logs every mutation.
        */
        Route::middleware(['tenant', 'audit'])->prefix('firm')->group(function () {
            Route::get('/', [FirmController::class, 'show'])->name('firm.show');
            Route::patch('/', [FirmController::class, 'update'])
                ->middleware('role:firm_owner')
                ->name('firm.update');

            Route::get('team', [FirmController::class, 'team'])->name('firm.team');
            Route::patch('team/{user}/suspend', [FirmController::class, 'suspendMember'])
                ->middleware('role:firm_owner')
                ->name('firm.team.suspend');

            // Module 12: Subscription System
            Route::patch('plan', [PlanController::class, 'changePlan'])
                ->middleware('role:firm_owner')
                ->name('firm.plan.update');

            // Module 8: Client Portal — dashboard aggregation for the client role
            Route::get('portal/dashboard', [PortalController::class, 'dashboard'])->name('firm.portal.dashboard');

            Route::get('invitations', [InvitationController::class, 'index'])
                ->middleware('role:firm_owner')
                ->name('firm.invitations.index');
            Route::post('invitations', [InvitationController::class, 'store'])
                ->middleware('role:firm_owner')
                ->name('firm.invitations.store');
            Route::delete('invitations/{invitation}', [InvitationController::class, 'destroy'])
                ->middleware('role:firm_owner')
                ->name('firm.invitations.destroy');

            // Module 3: Client Management
            Route::get('clients', [ClientController::class, 'index'])->name('firm.clients.index');
            Route::post('clients', [ClientController::class, 'store'])->name('firm.clients.store');
            Route::get('clients/{client}', [ClientController::class, 'show'])->name('firm.clients.show');
            Route::patch('clients/{client}', [ClientController::class, 'update'])->name('firm.clients.update');
            Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('firm.clients.destroy');
            Route::post('clients/{client}/notes', [ClientController::class, 'storeNote'])->name('firm.clients.notes.store');

            // Module 4: Case Management
            Route::get('cases', [CaseController::class, 'index'])->name('firm.cases.index');
            Route::post('cases', [CaseController::class, 'store'])->name('firm.cases.store');
            Route::get('cases/{case}', [CaseController::class, 'show'])->name('firm.cases.show');
            Route::patch('cases/{case}', [CaseController::class, 'update'])->name('firm.cases.update');
            Route::delete('cases/{case}', [CaseController::class, 'destroy'])->name('firm.cases.destroy');
            Route::patch('cases/{case}/status', [CaseController::class, 'updateStatus'])->name('firm.cases.status');
            Route::post('cases/{case}/team', [CaseController::class, 'assignTeam'])->name('firm.cases.team.store');
            Route::delete('cases/{case}/team/{user}', [CaseController::class, 'removeTeamMember'])->name('firm.cases.team.destroy');
            Route::post('cases/{case}/notes', [CaseController::class, 'storeNote'])->name('firm.cases.notes.store');

            // Module 5: Document Management
            Route::get('documents', [DocumentController::class, 'index'])->name('firm.documents.index');
            Route::post('documents', [DocumentController::class, 'store'])->name('firm.documents.store');
            Route::get('documents/{document}', [DocumentController::class, 'show'])->name('firm.documents.show');
            Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('firm.documents.download');
            Route::get('documents/{document}/versions', [DocumentController::class, 'versions'])->name('firm.documents.versions.index');
            Route::post('documents/{document}/versions', [DocumentController::class, 'storeVersion'])->name('firm.documents.versions.store');
            Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('firm.documents.destroy');
            Route::get('document-folders', [DocumentController::class, 'indexFolders'])->name('firm.document-folders.index');
            Route::post('document-folders', [DocumentController::class, 'storeFolder'])->name('firm.document-folders.store');

            // Module 6: Task & Deadline Management
            Route::get('tasks', [TaskController::class, 'index'])->name('firm.tasks.index');
            Route::post('tasks', [TaskController::class, 'store'])->name('firm.tasks.store');
            Route::get('tasks/{task}', [TaskController::class, 'show'])->name('firm.tasks.show');
            Route::patch('tasks/{task}', [TaskController::class, 'update'])->name('firm.tasks.update');
            Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('firm.tasks.destroy');

            // Module 7: Court Calendar System
            Route::get('calendar', [CourtEventController::class, 'index'])->name('firm.calendar.index');
            Route::post('calendar', [CourtEventController::class, 'store'])->name('firm.calendar.store');
            Route::get('calendar/{event}', [CourtEventController::class, 'show'])->name('firm.calendar.show');
            Route::patch('calendar/{event}', [CourtEventController::class, 'update'])->name('firm.calendar.update');
            Route::delete('calendar/{event}', [CourtEventController::class, 'destroy'])->name('firm.calendar.destroy');

            // Module 9: Communication System
            Route::get('conversations', [ConversationController::class, 'index'])->name('firm.conversations.index');
            Route::post('conversations', [ConversationController::class, 'store'])->name('firm.conversations.store');
            Route::get('conversations/{conversation}', [ConversationController::class, 'show'])->name('firm.conversations.show');
            Route::post('conversations/{conversation}/messages', [ConversationController::class, 'storeMessage'])->name('firm.conversations.messages.store');

            // Module 10: Billing & Time Tracking
            Route::get('time-entries', [BillingController::class, 'indexTimeEntries'])->name('firm.time-entries.index');
            Route::post('time-entries', [BillingController::class, 'storeTimeEntry'])->name('firm.time-entries.store');
            Route::post('expenses', [BillingController::class, 'storeExpense'])->name('firm.expenses.store');
            Route::get('invoices', [BillingController::class, 'indexInvoices'])->name('firm.invoices.index');
            Route::post('invoices', [BillingController::class, 'storeInvoice'])->name('firm.invoices.store');
            Route::get('invoices/{invoice}', [BillingController::class, 'showInvoice'])->name('firm.invoices.show');
            Route::patch('invoices/{invoice}/status', [BillingController::class, 'updateInvoiceStatus'])->name('firm.invoices.status');

            // Module 11: Reporting System (Firm Owner only, enforced via viewReports Gate)
            Route::prefix('reports')->group(function () {
                Route::get('cases', [ReportController::class, 'cases'])->name('firm.reports.cases');
                Route::get('workload', [ReportController::class, 'workload'])->name('firm.reports.workload');
                Route::get('revenue', [ReportController::class, 'revenue'])->name('firm.reports.revenue');
                Route::get('billing-status', [ReportController::class, 'billingStatus'])->name('firm.reports.billing-status');
                Route::get('case-performance', [ReportController::class, 'casePerformance'])->name('firm.reports.case-performance');
            });
        });

        // Future modules attach here: subscription plan management, client
        // portal dashboard aggregation — each under ->middleware(['tenant', 'audit']).
    });
});
