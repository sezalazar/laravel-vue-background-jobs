<?php

namespace App\Console\Commands;

use App\Models\BackgroundJob;
use App\Services\BackgroundJobValidator;
use App\Services\RetryHandler;
use App\Services\BackgroundJobLogger;
use App\Services\JobLogService;
use Illuminate\Console\Command;
use Throwable;

class RunBackgroundJobCommand extends Command
{
    protected $signature = 'background:execute
                            {class : Fully qualified class name}
                            {method : Method to execute}
                            {params?* : Parameters for the method}';

    protected $description = 'Run background jobs';

    public function __construct(
        private BackgroundJobValidator $validator,
        private BackgroundJobLogger $logger,
        private RetryHandler $retryHandler,
        private JobLogService $jobLogService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $className = $this->argument('class');
        $methodName = $this->argument('method');
        $params = $this->argument('params') ?? [];

        $this->logger->log('background_jobs', "Start executing {$className}::{$methodName}");

        $workingJob = BackgroundJob::where('class', $className)
            ->where('method', $methodName)
            ->where('status', 'pending')
            ->lockForUpdate()
            ->first();

        if (!$workingJob) {
            $this->logger->error('background_jobs_errors', "No pending job found for {$className}::{$methodName}");
            return 1;
        }

        $jobId = $workingJob->id;

        try {
            $maxRetries = config("background_jobs.allowed_classes.{$className}.retries", config('background_jobs.default_retries'));
            $delay = config("background_jobs.allowed_classes.{$className}.delay", config('background_jobs.default_delay'));

            $this->retryHandler->configure($maxRetries, $delay, $workingJob);

            $this->retryHandler->run(function () use ($className, $methodName, $params, $jobId, $workingJob) {
                $this->executeJob($className, $methodName, $params, $jobId, $workingJob);
            });

            return 0;
        } catch (Throwable $e) {
            BackgroundJob::where('id', $jobId)->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $this->jobLogService->create($jobId, "Job {$jobId} permanently failed after retries: {$e->getMessage()}");

            $this->logger->error('background_jobs_errors', "Job permanently failed: {$className}::{$methodName}", [
                'exception' => $e->getMessage(),
            ]);

            return 1;
        }
    }

    private function executeJob(string $className, string $methodName, array $params, int $jobId, BackgroundJob $workingJob): void
    {
        $this->logger->log('background_jobs', "Executing {$className}::{$methodName} for Job ID {$jobId}");
        $this->jobLogService->create($jobId, "Job {$jobId} is running");

        BackgroundJob::where('id', $jobId)->update(['status' => 'running']);

        if (!$this->validator->validate($className, $methodName)) {
            throw new \InvalidArgumentException("Validations failed for {$className}::{$methodName}");
        }

        $instance = app()->make($className);
        call_user_func_array([$instance, $methodName], $params);

        BackgroundJob::where('id', $jobId)->update([
            'status' => 'completed',
        ]);

        $this->jobLogService->create($jobId, "Job {$jobId} completed successfully");
        $this->logger->log('background_jobs', "Job {$jobId} completed successfully");
    }
}