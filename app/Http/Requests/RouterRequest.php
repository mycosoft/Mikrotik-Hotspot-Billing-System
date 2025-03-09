<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RouterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'ip_address' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
            'description' => 'nullable|string',
            'coordinates' => 'nullable|string',
            'coverage' => 'nullable|string',
            'is_active' => 'required|boolean'
        ];
    }

    protected function prepareForValidation()
    {
        // Add port to IP if not specified
        if ($this->has('ip_address') && !str_contains($this->ip_address, ':')) {
            $this->merge([
                'ip_address' => $this->ip_address . ':8728'
            ]);
        }
    }
} 