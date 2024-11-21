<?php

namespace App\Services;

use App\Models\JobLog;

class JobLogService
{
    public function create(int $backgroundJobId, string $message): void
    {
        JobLog::create([
            'background_job_id' => $backgroundJobId,
            'message' => $message,
        ]);
    }
}
