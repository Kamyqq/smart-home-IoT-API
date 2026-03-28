<?php

namespace App\Listeners;

use App\Events\FireAlertEvent;
use App\Events\HighHumidityEvent;
use App\Notifications\FireAlertNotification;
use App\Notifications\HumidityAlertNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use function Laravel\Prompts\notify;

class ProcessHighHumidity implements ShouldQueue
{
    public function __construct() {}

    public function handle(HighHumidityEvent $event): void
    {
        $event->owner->notify(
            new HumidityAlertNotification($event->room, $event->humidity)
        );
    }
}
