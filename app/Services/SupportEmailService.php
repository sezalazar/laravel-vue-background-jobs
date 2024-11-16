<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\BackgroundJobFailedMail;

class SupportEmailService
{
    public function sendFailureNotification(string $className, string $methodName, int $attempts, string $errorMessage): void
    {
        $recipient = $this->getRecipientEmail();

        Mail::to($recipient)->send(new BackgroundJobFailedMail($className, $methodName, $attempts, $errorMessage));
    }

    private function getRecipientEmail(): string
    {
        $recipient = config('app.support_email');

        if (!$recipient) {
            throw new \RuntimeException('The support email address is not configured in .env file.');
        }

        return $recipient;
    }
}
