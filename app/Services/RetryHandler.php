<?php

namespace App\Services;

use App\Models\BackgroundJob;
use Throwable;

class RetryHandler
{
    private int $retries = 0;
    private int $delay = 0;
    private ?BackgroundJob $job = null;

    public function __construct(private BackgroundJobLogger $logger) {}

    public function configure(int $retries, int $delay, BackgroundJob $job): void
    {
        $this->retries = $retries;
        $this->delay = $delay;
        $this->job = $job;
        $job->retry_count = 0;
        $job->save();
    }

    public function run(callable $callback): void
    {
        $attempts = 0;

        while (!$this->hasExceededRetries($attempts)) {
            try {
                $currentAttempt = $attempts + 1;
                $this->logger->log('background_jobs', "Attempt {$currentAttempt} starting.");
                $callback();
                $this->job->retry_count = $attempts;
                $this->job->save();
                $this->logger->log('background_jobs', "Attempt {$currentAttempt} succeeded.");
                return;
            } catch (Throwable $e) {
                $attempts++;
                $this->job->retry_count = $attempts;
                $this->job->save();

                $this->logger->error('background_jobs_errors', "Attempt {$attempts} failed.", [
                    'exception' => $e->getMessage(),
                ]);

                if ($this->hasExceededRetries($attempts)) {
                    $this->logger->error('background_jobs_errors', "All retries exhausted after {$attempts} attempts.");
                    throw $e;
                }

                sleep($this->delay);
            }
        }
    }

    private function hasExceededRetries(int $attempts): bool
    {
        return $attempts >= $this->retries;
    }
}
