<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:250',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,process,completed'
        ];
    }
}
