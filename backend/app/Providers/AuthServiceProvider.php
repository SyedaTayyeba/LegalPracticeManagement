<?php

namespace App\Providers;

use App\Models\CaseFile;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\CourtEvent;
use App\Models\Document;
use App\Models\Firm;
use App\Models\Invitation;
use App\Models\Invoice;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use App\Policies\CasePolicy;
use App\Policies\ClientPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\CourtEventPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\FirmPolicy;
use App\Policies\InvitationPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\TaskPolicy;
use App\Policies\TimeEntryPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Firm::class => FirmPolicy::class,
        User::class => UserPolicy::class,
        Invitation::class => InvitationPolicy::class,
        Client::class => ClientPolicy::class,
        CaseFile::class => CasePolicy::class,
        Document::class => DocumentPolicy::class,
        Task::class => TaskPolicy::class,
        CourtEvent::class => CourtEventPolicy::class,
        Conversation::class => ConversationPolicy::class,
        Invoice::class => InvoicePolicy::class,
        TimeEntry::class => TimeEntryPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        \Illuminate\Support\Facades\Gate::define(
            'viewReports',
            [\App\Policies\ReportPolicy::class, 'viewReports']
        );
    }
}
