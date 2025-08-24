<?php

namespace App\Http\Requests\Sales\Clients;

use Illuminate\Foundation\Http\FormRequest;

class SearchClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}