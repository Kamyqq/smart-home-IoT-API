<?php

namespace App\Notifications;

use App\Models\Room;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HumidityAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Room $room,
        public float $humidity,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('HIGH HUMIDITY ALERT: ' . $this->room->name)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Unusually high air humidity has been detected in room: **' . $this->room->name . '**.')
            ->line('The registered humidity level is: **' . $this->humidity . '%**.')
            ->action('Check Room Status', route('rooms.measurements', ['room' => $this->room->id]))
            ->line('Please ventilate the room to prevent mold growth and structural damage.');
    }
}
