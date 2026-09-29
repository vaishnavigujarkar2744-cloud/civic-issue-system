<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public Complaint $complaint, public string $message)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Complaint #{$this->complaint->id}: {$this->complaint->status_label}")
            ->greeting("Hello {$notifiable->name},")
            ->line($this->message)
            ->line("Complaint: {$this->complaint->title}")
            ->action('View complaint', $notifiable->complaintUrl($this->complaint->id))
            ->line('Thank you for helping improve our city.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'complaint_id' => $this->complaint->id,
            'title' => $this->complaint->title,
            'status' => $this->complaint->status,
            'message' => $this->message,
        ];
    }
}
