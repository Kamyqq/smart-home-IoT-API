<?php

namespace App\Listeners;

use App\Events\FireAlertEvent;
use App\Notifications\FireAlertNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use function Laravel\Prompts\notify;

class ProcessFireAlarm implements ShouldQueue
{
    public function __construct() {}

    public function handle(FireAlertEvent $event): void
    {
        $event->owner->notify(
            new FireAlertNotification($event->room, $event->temperature)
        );
    }
}
