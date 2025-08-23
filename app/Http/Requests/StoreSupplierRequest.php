<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreSupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo usuarios admin pueden crear proveedores
        return Auth::check() && Auth::user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:suppliers',
            'phone' => 'nullable|string|max:20|regex:/^[\d\s\-\+\(\)]+$/',
            'address' => 'nullable|string|max:500',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del proveedor es obligatorio',
            'name.string' => 'El nombre del proveedor debe ser texto',
            'name.max' => 'El nombre del proveedor no puede tener más de 255 caracteres',
            'email.required' => 'El email del proveedor es obligatorio',
            'email.string' => 'El email debe ser texto',
            'email.email' => 'El email debe tener un formato válido',
            'email.max' => 'El email no puede tener más de 255 caracteres',
            'email.unique' => 'Ya existe un proveedor con este email',
            'phone.string' => 'El teléfono debe ser texto',
            'phone.max' => 'El teléfono no puede tener más de 20 caracteres',
            'phone.regex' => 'El teléfono debe tener un formato válido (solo números, espacios, guiones, paréntesis y +)',
            'address.string' => 'La dirección debe ser texto',
            'address.max' => 'La dirección no puede tener más de 500 caracteres',
            'is_active.boolean' => 'El estado debe ser verdadero o falso',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Limpiar el teléfono de caracteres innecesarios
        if ($this->has('phone')) {
            $phone = preg_replace('/[^\d\s\-\+\(\)]/', '', $this->phone);
            $this->merge(['phone' => $phone]);
        }

        // Asegurar que el proveedor esté activo por defecto
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }
}
