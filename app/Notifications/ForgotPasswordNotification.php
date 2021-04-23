<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ForgotPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url('password/reset?token=' . $this->token);

        return (new MailMessage)
            ->subject('Wachtwoord resetten')
            ->greeting('Hallo,')
            ->line('U ontvangt deze e-mail omdat we een verzoek voor het opnieuw instellen van uw wachtwoord voor uw account hebben ontvangen.')
            ->action('Hertel wachtwoord', $url);
    }
}
