<?php

namespace App\Notifications;

use App\Models\Reply;
use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReplyNotification extends Notification
{
    public function __construct(
        public Ticket $ticket,
        public Reply $reply,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nieuwe reactie op je ticket')
            ->greeting('Hallo ' . $notifiable->first_name . ',')
            ->line('Er is een nieuwe reactie geplaatst op je ticket "' . $this->ticket->title . '".')
            ->line('"' . $this->reply->body . '"')
            ->action('Bekijk ticket', url('/tickets/' . $this->ticket->id))
            ->line('Je kunt het ticket en de reactie bekijken in het Klantenhulpportaal.')
            ->salutation('Met vriendelijke groet,' . PHP_EOL . 'Klantenhulpportaal');
    }
}
