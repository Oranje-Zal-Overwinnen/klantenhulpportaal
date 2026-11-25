<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     */
    protected function resetUrl($notifiable): string
    {
        return url('/reset-password?'.http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]));
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = $this->resetUrl($notifiable);
        $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return new MailMessage()
            ->subject('Wachtwoord opnieuw instellen')
            ->greeting('Hallo '.$notifiable->first_name.',')
            ->line('Je ontvangt deze e-mail omdat er een verzoek is gedaan om je wachtwoord opnieuw in te stellen.')
            ->action('Wachtwoord opnieuw instellen', $resetUrl)
            ->line('Deze link is '.$expireMinutes.' minuten geldig.')
            ->line('Heb je dit niet aangevraagd? Dan kun je deze e-mail negeren.')
            ->salutation('Met vriendelijke groet,'.PHP_EOL.'Klantenhulpportaal');
    }
}
