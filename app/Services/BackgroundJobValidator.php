<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class BackgroundJobValidator
{
    public function validate(string $className, string $methodName): bool
    {
        if (!$this->isValidClassName($className) || !$this->isValidMethodName($methodName)) {
            Log::error("Invalid class or method name format: {$className}::{$methodName}");
            return false;
        }

        $allowedClasses = config('background_jobs.allowed_classes', []);
        if (!array_key_exists($className, $allowedClasses)) {
            Log::error("Unauthorized class attempted: {$className}");
            return false;
        }

        if (!method_exists($className, $methodName)) {
            Log::error("Method does not exist: {$className}::{$methodName}");
            return false;
        }

        Log::info("Validation passed for {$className}::{$methodName}");
        return true;
    }

    private function isValidClassName(string $className): bool
    {
        $pattern = '/^[A-Za-z_\\\\][A-Za-z0-9_\\\\]*$/';
        return preg_match($pattern, $className) === 1;
    }

    private function isValidMethodName(string $methodName): bool
    {
        $pattern = '/^[A-Za-z_][A-Za-z0-9_]*$/';
        return preg_match($pattern, $methodName) === 1;
    }
}
