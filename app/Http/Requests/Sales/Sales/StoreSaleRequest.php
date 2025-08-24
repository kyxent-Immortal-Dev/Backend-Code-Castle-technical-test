<?php

namespace App\Http\Requests\Sales\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'sale_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'sale_details' => 'required|array|min:1',
            'sale_details.*.product_id' => 'required|exists:products,id',
            'sale_details.*.quantity' => 'required|integer|min:1',
            'sale_details.*.sale_price' => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'El cliente es obligatorio.',
            'client_id.exists' => 'El cliente seleccionado no existe.',
            'sale_date.date' => 'La fecha de venta debe tener un formato válido.',
            'notes.max' => 'Las notas no pueden tener más de 1000 caracteres.',
            'sale_details.required' => 'Los detalles de la venta son obligatorios.',
            'sale_details.min' => 'Debe haber al menos un producto en la venta.',
            'sale_details.*.product_id.required' => 'El producto es obligatorio.',
            'sale_details.*.product_id.exists' => 'El producto seleccionado no existe.',
            'sale_details.*.quantity.required' => 'La cantidad es obligatoria.',
            'sale_details.*.quantity.integer' => 'La cantidad debe ser un número entero.',
            'sale_details.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
            'sale_details.*.sale_price.required' => 'El precio de venta es obligatorio.',
            'sale_details.*.sale_price.numeric' => 'El precio de venta debe ser un número.',
            'sale_details.*.sale_price.min' => 'El precio de venta debe ser mayor a 0.',
        ];
    }
}