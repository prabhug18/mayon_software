<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SmsMessage;
use Illuminate\Notifications\Notification;

class UserRegisteredNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        // You can pass user data here if needed
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'sms'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Laravel Boilerplate')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your account has been successfully registered.')
            ->action('Login', url('/login'))
            ->line('Thank you for joining us!');
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): SmsMessage
    {
        return (new SmsMessage)
            ->content('Welcome ' . $notifiable->name . '! Your account has been registered at Laravel Boilerplate.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
