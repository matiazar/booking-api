<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Throwable;

/**
 * Sent synchronously (not queued): if the queue itself is what broke,
 * the admin still needs to hear about it. Only plain strings are kept,
 * never the exception object, so the notification is always serializable.
 */
final class ExceptionOccurredNotification extends Notification
{
    public readonly string $exceptionClass;

    public readonly string $exceptionMessage;

    public readonly string $location;

    public readonly string $trace;

    public function __construct(Throwable $exception, public readonly ?string $url = null)
    {
        $this->exceptionClass = $exception::class;
        $this->exceptionMessage = $exception->getMessage();
        $this->location = $exception->getFile().':'.$exception->getLine();
        $this->trace = implode("\n", array_slice(explode("\n", $exception->getTraceAsString()), 0, 15));
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('['.config('app.name').'] '.class_basename($this->exceptionClass))
            ->line("**{$this->exceptionClass}**: {$this->exceptionMessage}")
            ->line("At: {$this->location}")
            ->line('URL: '.($this->url ?? 'n/a (console / queue)'))
            ->line("```\n{$this->trace}\n```");
    }
}
