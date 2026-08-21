<?php

namespace App\Http\Requests\Admin\Inventory;

use App\Models\InventoryTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', Rule::in([
                InventoryTransaction::TYPE_ADDITION,
                InventoryTransaction::TYPE_DEDUCTION,
                InventoryTransaction::TYPE_ADJUSTMENT,
            ])],
            'quantity' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
            'reference_id' => ['nullable', 'string', 'max:100'],
        ];
    }
}
