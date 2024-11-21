<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ExampleService
{
    public function exampleMethod(...$params)
    {
        Log::info("This is the exampleMethod");
    }

    public function secondMethod(...$params)
    {
        Log::info("This is the secondMethod example with params: ", $params);
    }
}
