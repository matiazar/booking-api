<?php

namespace App\Support;

use App\Notifications\ExceptionOccurredNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Emails unexpected exceptions to the admin, at most once per exception
 * signature per throttle window, so an error loop doesn't flood the inbox.
 *
 * Wired in bootstrap/app.php. It never throws: a failing reporter must
 * not hide the original error, which Laravel still logs as usual.
 */
final class AdminExceptionNotifier
{
    public function __invoke(Throwable $exception): void
    {
        $recipient = config('booking.admin_email');

        if (! config('booking.report_errors') || blank($recipient)) {
            return;
        }

        $fingerprint = $exception::class.'|'.$exception->getFile().'|'.$exception->getLine();
        $signature = 'admin-exception-mail:'.hash('sha256', $fingerprint);

        try {
            $window = now()->addMinutes((int) config('booking.error_mail_throttle_minutes'));

            if (! Cache::add($signature, true, $window)) {
                return;
            }

            $url = app()->runningInConsole() ? null : request()->url();

            Notification::route('mail', $recipient)
                ->notify(new ExceptionOccurredNotification($exception, $url));
        } catch (Throwable $reportingFailure) {
            Log::warning('Could not email exception report to admin.', [
                'original' => $exception::class,
                'reason' => $reportingFailure->getMessage(),
            ]);
        }
    }
}
