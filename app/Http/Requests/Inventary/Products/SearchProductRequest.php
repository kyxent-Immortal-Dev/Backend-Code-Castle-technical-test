<?php

namespace App\Http\Requests\Inventary\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SearchProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Cualquier usuario autenticado puede buscar productos
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'min_price' => 'sometimes|numeric|min:0|max:999999.99',
            'max_price' => 'sometimes|numeric|min:0|max:999999.99',
            'in_stock' => 'sometimes|boolean',
            'low_stock' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort_by' => 'sometimes|in:name,unit_price,stock,created_at',
            'sort_order' => 'sometimes|in:asc,desc',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.string' => 'El nombre debe ser texto',
            'name.max' => 'El nombre no puede tener más de 255 caracteres',
            'description.string' => 'La descripción debe ser texto',
            'description.max' => 'La descripción no puede tener más de 1000 caracteres',
            'min_price.numeric' => 'El precio mínimo debe ser un número',
            'min_price.min' => 'El precio mínimo debe ser mayor o igual a 0',
            'min_price.max' => 'El precio mínimo no puede ser mayor a 999,999.99',
            'max_price.numeric' => 'El precio máximo debe ser un número',
            'max_price.min' => 'El precio máximo debe ser mayor o igual a 0',
            'max_price.max' => 'El precio máximo no puede ser mayor a 999,999.99',
            'in_stock.boolean' => 'El filtro de stock debe ser verdadero o falso',
            'low_stock.boolean' => 'El filtro de stock bajo debe ser verdadero o falso',
            'is_active.boolean' => 'El filtro de estado debe ser verdadero o falso',
            'page.integer' => 'La página debe ser un número entero',
            'page.min' => 'La página debe ser mayor a 0',
            'per_page.integer' => 'El número de elementos por página debe ser un número entero',
            'per_page.min' => 'El número de elementos por página debe ser mayor a 0',
            'per_page.max' => 'El número de elementos por página no puede ser mayor a 100',
            'sort_by.in' => 'El campo de ordenamiento debe ser: name, unit_price, stock o created_at',
            'sort_order.in' => 'El orden debe ser: asc o desc',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Validar que max_price sea mayor que min_price si ambos están presentes
        if ($this->has('min_price') && $this->has('max_price')) {
            if ($this->min_price > $this->max_price) {
                $this->merge(['max_price' => $this->min_price]);
            }
        }

        // Establecer valores por defecto
        if (!$this->has('sort_by')) {
            $this->merge(['sort_by' => 'name']);
        }

        if (!$this->has('sort_order')) {
            $this->merge(['sort_order' => 'asc']);
        }
    }
}
