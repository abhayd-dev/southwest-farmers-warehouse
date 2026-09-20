<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;

class ReceivePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_number' => 'required|string',
            'duties' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'taxes' => 'nullable|numeric|min:0',
            'transportation_cost' => 'nullable|numeric|min:0',
            'demurrage' => 'nullable|numeric|min:0',
            'items' => 'required|array',
        ];
    }
}
