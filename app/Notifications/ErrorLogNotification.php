<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;


class ErrorLogNotification extends Notification
{
    use Queueable;


    public array $logData;



    public function __construct(array $logData)
    {
        $this->logData = $logData;
    }



    public function via($notifiable)
    {
        return [
            'mail'
        ];
    }



    public function toMail($notifiable)
    {

        return (new MailMessage)

            ->subject('🚨 Critical Laravel Error Detected')

            ->greeting('Laravel Log Monitoring Alert')

            ->line('A critical error has occurred.')

            ->line(
                'Level: '
                .$this->logData['level']
            )


            ->line(
                'Message: '
                .$this->logData['message']
            )


            ->line(
                'URL: '
                .($this->logData['url'] ?? 'N/A')
            )


            ->line(
                'IP Address: '
                .($this->logData['ip'] ?? 'N/A')
            )


            ->line(
                'User ID: '
                .($this->logData['user_id'] ?? 'Guest')
            )


            ->line(
                'Time: '
                .now()
            )


            ->action(
                'Open Log Dashboard',
                url('/logs/dashboard')
            )


            ->line(
                'Please check Kibana dashboard.'
            );

    }

}