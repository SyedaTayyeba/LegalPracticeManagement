<?php

namespace App\Listeners;

use App\Events\UserInvited;
use App\Notifications\FirmInvitationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;

class SendInvitationEmail implements ShouldQueue
{
    public function handle(UserInvited $event): void
    {
        (new AnonymousNotifiable())
            ->route('mail', $event->invitation->email)
            ->notify(new FirmInvitationNotification($event->invitation));
    }
}
