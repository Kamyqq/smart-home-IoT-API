<?php

namespace App\Notifications;

use App\Models\Room;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FireAlertNotification extends Notification
{
    public function __construct(
        public Room $room,
        public float $temperature,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('FIRE ALARM: ' . $this->room->name)
            ->greeting('Warning ' . $notifiable->name . '!')
            ->line('A critically high temperature has been detected in room: **' . $this->room->name . '**.')
            ->line('The registered temperature is: **' . $this->temperature . ' °C**.')
            ->action('Open Emergency Panel', route('rooms.measurements', ['room' => $this->room->id]))
            ->line('Take immediate action to ensure safety and prevent property damage.');
    }
}
