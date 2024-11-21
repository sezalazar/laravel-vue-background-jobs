<?php

use Illuminate\Support\Facades\Artisan;
use App\Services\BackgroundJobLogger;

if (!function_exists('runBackgroundJobHelper')) {
    function runBackgroundJobHelper(string $className, string $methodName, array $params = []): void
    {
        $logger = app(BackgroundJobLogger::class);
        $logger->log('background_jobs', "Start executing {$className}::{$methodName}");

        try {
            Artisan::call('background:execute', [
                'class' => $className,
                'method' => $methodName,
                'params' => $params,
            ]);

            $output = Artisan::output();
            $logger->log('background_jobs', "Command executed successfully. Output: {$output}");
        } catch (\Exception $e) {
            $logger->error('background_jobs_errors', "Failed to execute command for {$className}::{$methodName}", [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    // TODO: Improve and use functions below
    // function isRunningInDocker(): bool
    // {
    //     return file_exists('/.dockerenv') || strpos(file_get_contents('/proc/1/cgroup'), 'docker') !== false;
    // }

    // function isRunningInsideContainer(): bool
    // {
    //     return file_exists('/.dockerenv');
    // }

    // function isWindowsOs(): bool
    // {
    //     return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    // }

    // function runCommandOnWindows(string $command): void
    // {
    //     $logger = app(\App\Services\BackgroundJobLogger::class);
    //     $logger->log('background_jobs', "Running command on Windows: {$command}");
    //     exec("start /B {$command}");
    // }


    // function runCommandOnUnix(string $command): void
    // {
    //     $logger = app(\App\Services\BackgroundJobLogger::class);
    //     $logger->log('background_jobs', "Running command on Unix/Linux: {$command}");
    //     $logFile = '/var/www/html/storage/logs/background_job.log';
    //     // exec("{$command} > {$logFile} 2>&1 &");

    //     $logger->log('background_jobs', "Command executed: {$command}");
    // }
}
