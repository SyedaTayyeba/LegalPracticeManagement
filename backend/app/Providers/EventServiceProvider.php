<?php

namespace App\Providers;

use App\Events\UserInvited;
use App\Events\UserRegistered;
use App\Listeners\SendInvitationEmail;
use App\Listeners\SendWelcomeNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserRegistered::class => [
            SendWelcomeNotification::class,
        ],
        UserInvited::class => [
            SendInvitationEmail::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
