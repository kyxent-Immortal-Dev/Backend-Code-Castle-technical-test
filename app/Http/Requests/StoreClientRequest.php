<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del cliente es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'email.required' => 'El email del cliente es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'Ya existe un cliente con este email.',
            'phone.max' => 'El teléfono no puede tener más de 20 caracteres.',
        ];
    }
}