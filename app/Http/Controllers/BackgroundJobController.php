<?php

namespace App\Http\Controllers;

use App\Http\Requests\RunBackgroundJobRequest;
use Illuminate\Http\Request;
use App\Models\BackgroundJob;
use App\Services\BackgroundJobLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use ReflectionClass;
use App\DTOs\BackgroundJobDTO;

class BackgroundJobController extends Controller
{

    public function __construct(private BackgroundJobLogger $logger)
    {}
    public function index()
    {
        return BackgroundJob::all();
    }

    public function cancel(BackgroundJob $job): JsonResponse
    {
        if (!$job->isRunning()) {
            return response()->json(['error' => 'Job is not running'], Response::HTTP_BAD_REQUEST);
        }

        $job->update(['status' => 'cancelled']);

        return response()->json(
            ['message' => 'Job cancelled successfully'], 
            Response::HTTP_OK
        );
    }

    public function getLogs(BackgroundJob $job): JsonResponse
    {
        $logs = $job->logs()->latest()->get();

        return response()->json($logs);
    }

    public function runBackgroundJob(RunBackgroundJobRequest $request, BackgroundJobLogger $logger): JsonResponse
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            $job = BackgroundJob::create([
                'class' => $validated['className'],
                'method' => $validated['methodName'],
                'params' => $validated['params'] ?? [],
                'status' => 'pending',
                'retry_count' => 0,
                'max_retries' => config("background_jobs.allowed_classes.{$validated['className']}.retries", config('background_jobs.default_retries')),
                'priority' => config("background_jobs.allowed_classes.{$validated['className']}.priority", config('background_jobs.default_priority')),
                'scheduled_at' => now(),
                'user_id' => Auth::id(),
            ]);

            $jobDTO = BackgroundJobDTO::fromModel($job);

            runBackgroundJobHelper($jobDTO);


            DB::commit();

            $logger->log('background_jobs', 'Background job created successfully', ['job_id' => $job->id]);

            return response()->json(['message' => 'Background job created successfully', 'job' => $job], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            $logger->error('background_jobs', 'Failed creating background job', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json(['error' => 'Failed creating background job. Please try again.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAllowedClasses(): JsonResponse
    {
        $allowedClasses = Config::get('background_jobs.allowed_classes', []);
        return response()->json($allowedClasses);
    }

    public function getClassMethods(Request $request): JsonResponse
    {
        $className = $request->input('className');

        if (!class_exists($className)) {
            return response()->json(['error' => 'Class does not exist.'], Response::HTTP_BAD_REQUEST);
        }

        $reflection = new ReflectionClass($className);
        $methods = collect($reflection->getMethods(\ReflectionMethod::IS_PUBLIC))
            ->filter(function ($method) use ($className) {
                return $method->class === $className; 
            })
            ->pluck('name')
            ->values()
            ->all();

        return response()->json($methods);
    }

    public function getMethodParameters(Request $request): JsonResponse
    {
        $className = $request->input('className');
        $methodName = $request->input('methodName');

        if (!class_exists($className) || !method_exists($className, $methodName)) {
            return response()->json(['error' => 'Invalid class or method'], Response::HTTP_BAD_REQUEST);
        }

        $reflectionMethod = new \ReflectionMethod($className, $methodName);
        $parameters = [];

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $parameters[] = [
                'name' => $parameter->getName(),
                'type' => (string) $parameter->getType() ?: 'mixed',
                'optional' => $parameter->isOptional(),
                'default' => $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
            ];
        }

        return response()->json($parameters);
    }

    public function retryJob(BackgroundJob $job): JsonResponse
    {
        if ($job->status !== 'failed') {
            return response()->json(['error' => 'Only failed jobs can be retried'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $job->update(['status' => 'pending', 'retry_count' => 0]);
            runBackgroundJobHelper($job->class, $job->method, $job->params);

            $this->logger->log('background_jobs', 'Job retried successfully', ['job_id' => $job->id]);

            return response()->json(['message' => 'Job retried successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('background_jobs_errors', 'Failed to retry job', [
                'job_id' => $job->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Failed to retry job. Please try again.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
