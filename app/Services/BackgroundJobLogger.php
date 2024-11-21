<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class BackgroundJobLogger
{
    public function log(string $channel, string $message, array $context = []): void
    {
        Log::channel($channel)->info($message, $context);
    }

    public function error(string $channel, string $message, array $context = []): void
    {
        Log::channel($channel)->error($message, $context);
    }
}