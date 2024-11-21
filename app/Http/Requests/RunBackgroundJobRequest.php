<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RunBackgroundJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'className' => 'required|string',
            'methodName' => 'required|string',
            'params' => 'nullable|array',
        ];
    }
}
