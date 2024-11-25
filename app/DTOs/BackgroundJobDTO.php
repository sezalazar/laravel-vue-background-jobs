<?php

namespace App\DTOs;

class BackgroundJobDTO
{
    public function __construct(
        public int $id,
        public string $class,
        public string $method,
        public array $params,
        public string $status,
        public int $priority,
        public string $scheduledAt,
        public ?int $userId
    ) {}

    public static function fromModel($job): self
    {
        return new self(
            $job->id,
            $job->class,
            $job->method,
            $job->params,
            $job->status,
            $job->priority,
            $job->scheduled_at,
            $job->user_id
        );
    }
}
