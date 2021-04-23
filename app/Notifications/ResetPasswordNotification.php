<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Wachtwoord gewijzigd')
            ->line('Het wachtwoord voor uw account is gewijzigd')
            ->line('Als dit klopt hoeft u niks te doen')
            ->line('Als u uw wachtwoord niet gewijzigd heeft moet u direct contact op nemen met ' . config('app.name'));
    }
}
