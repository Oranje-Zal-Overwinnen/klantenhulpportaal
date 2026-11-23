<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Bevestig je e-mailadres')
            ->greeting('Hallo ' . $notifiable->first_name . ',')
            ->line('Bedankt voor je registratie bij het Klantenhulpportaal.')
            ->line('Bevestig je e-mailadres met de knop hieronder.')
            ->action('E-mailadres bevestigen', $verificationUrl)
            ->line('Als je geen account hebt aangemaakt, kun je deze e-mail negeren.')
            ->salutation('Met vriendelijke groet,' . PHP_EOL . 'Klantenhulpportaal');
    }
}
