<?php

namespace App\Http\Requests\Inventary;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo usuarios admin pueden actualizar compras
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
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'user_id' => 'required|integer|exists:users,id',
            'purchase_date' => 'required|date|before_or_equal:today',
            'total_amount' => 'required|numeric|min:0.01',
            'status' => 'required|string|in:pending,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|integer|exists:products,id',
            'details.*.quantity' => 'required|integer|min:1|max:999999',
            'details.*.purchase_price' => 'required|numeric|min:0.01|max:999999.99',
            'details.*.subtotal' => 'required|numeric|min:0.01',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'supplier_id.required' => 'El proveedor es obligatorio',
            'supplier_id.integer' => 'El ID del proveedor debe ser un número entero',
            'supplier_id.exists' => 'El proveedor seleccionado no existe',
            'user_id.required' => 'El usuario es obligatorio',
            'user_id.integer' => 'El ID del usuario debe ser un número entero',
            'user_id.exists' => 'El usuario seleccionado no existe',
            'purchase_date.required' => 'La fecha de compra es obligatoria',
            'purchase_date.date' => 'La fecha de compra debe ser una fecha válida',
            'purchase_date.before_or_equal' => 'La fecha de compra no puede ser futura',
            'total_amount.required' => 'El monto total es obligatorio',
            'total_amount.numeric' => 'El monto total debe ser un número',
            'total_amount.min' => 'El monto total debe ser mayor a 0',
            'status.required' => 'El estado es obligatorio',
            'status.string' => 'El estado debe ser texto',
            'status.in' => 'El estado debe ser: pendiente, completada o cancelada',
            'notes.string' => 'Las notas deben ser texto',
            'notes.max' => 'Las notas no pueden tener más de 1000 caracteres',
            'details.required' => 'Los detalles de la compra son obligatorios',
            'details.array' => 'Los detalles deben ser una lista',
            'details.min' => 'Debe haber al menos un producto en la compra',
            'details.*.product_id.required' => 'El ID del producto es obligatorio',
            'details.*.product_id.integer' => 'El ID del producto debe ser un número entero',
            'details.*.product_id.exists' => 'El producto seleccionado no existe',
            'details.*.quantity.required' => 'La cantidad es obligatoria',
            'details.*.quantity.integer' => 'La cantidad debe ser un número entero',
            'details.*.quantity.min' => 'La cantidad debe ser mayor a 0',
            'details.*.quantity.max' => 'La cantidad no puede ser mayor a 999,999',
            'details.*.purchase_price.required' => 'El precio de compra es obligatorio',
            'details.*.purchase_price.numeric' => 'El precio de compra debe ser un número',
            'details.*.purchase_price.min' => 'El precio de compra debe ser mayor a 0',
            'details.*.purchase_price.max' => 'El precio de compra no puede ser mayor a 999,999.99',
            'details.*.subtotal.required' => 'El subtotal es obligatorio',
            'details.*.subtotal.numeric' => 'El subtotal debe ser un número',
            'details.*.subtotal.min' => 'El subtotal debe ser mayor a 0',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Si no se proporciona la fecha, usar la fecha actual
        if (!$this->has('purchase_date')) {
            $this->merge(['purchase_date' => now()->toDateString()]);
        }

        // Si no se proporcionan notas, usar null
        if (!$this->has('notes') || empty($this->notes)) {
            $this->merge(['notes' => null]);
        }

        // Asegurar que el status sea válido
        if ($this->has('status') && !in_array($this->status, ['pending', 'completed', 'cancelled'])) {
            $this->merge(['status' => 'pending']);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar que no haya productos duplicados en la compra
            $productIds = collect($this->details)->pluck('product_id');
            $duplicates = $productIds->duplicates();
            
            if ($duplicates->count() > 0) {
                $validator->errors()->add('details', 'No puede haber productos duplicados en la misma compra.');
            }

            // Validar que el proveedor esté activo
            $supplier = \App\Models\Supplier::find($this->supplier_id);
            if ($supplier && !$supplier->is_active) {
                $validator->errors()->add('supplier_id', 'El proveedor seleccionado está inactivo.');
            }

            // Validar que los productos estén activos
            $productIds = collect($this->details)->pluck('product_id');
            $inactiveProducts = \App\Models\Product::whereIn('id', $productIds)
                ->where('is_active', false)
                ->pluck('name');
            
            if ($inactiveProducts->count() > 0) {
                $validator->errors()->add('details', 'Los siguientes productos están inactivos: ' . $inactiveProducts->implode(', '));
            }

            // Validar que el usuario exista y esté activo
            $user = \App\Models\User::find($this->user_id);
            if (!$user) {
                $validator->errors()->add('user_id', 'El usuario seleccionado no existe.');
            } elseif (!$user->is_active) {
                $validator->errors()->add('user_id', 'El usuario seleccionado está inactivo.');
            }
        });
    }
} 