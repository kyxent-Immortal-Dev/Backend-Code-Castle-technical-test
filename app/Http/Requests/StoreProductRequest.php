<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo usuarios admin pueden crear productos
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
            'name' => 'required|string|max:255|unique:products',
            'description' => 'nullable|string|max:1000',
            'unit_price' => 'required|numeric|min:0|max:999999.99',
            'stock' => 'required|integer|min:0|max:999999',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio',
            'name.string' => 'El nombre del producto debe ser texto',
            'name.max' => 'El nombre del producto no puede tener más de 255 caracteres',
            'name.unique' => 'Ya existe un producto con este nombre',
            'description.string' => 'La descripción debe ser texto',
            'description.max' => 'La descripción no puede tener más de 1000 caracteres',
            'unit_price.required' => 'El precio unitario es obligatorio',
            'unit_price.numeric' => 'El precio unitario debe ser un número',
            'unit_price.min' => 'El precio unitario debe ser mayor o igual a 0',
            'unit_price.max' => 'El precio unitario no puede ser mayor a 999,999.99',
            'stock.required' => 'El stock inicial es obligatorio',
            'stock.integer' => 'El stock debe ser un número entero',
            'stock.min' => 'El stock debe ser mayor o igual a 0',
            'stock.max' => 'El stock no puede ser mayor a 999,999',
            'is_active.boolean' => 'El estado debe ser verdadero o falso',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Asegurar que el stock sea 0 si no se proporciona
        if (!$this->has('stock')) {
            $this->merge(['stock' => 0]);
        }

        // Asegurar que el producto esté activo por defecto
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }
}
