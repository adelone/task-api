<?php

namespace App\Http\Requests;
class StoreTaskRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:250',
            'description' => 'nullable|string',
        ];
    }
}
