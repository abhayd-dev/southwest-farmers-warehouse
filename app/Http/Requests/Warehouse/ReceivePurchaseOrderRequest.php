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
            'invoice_document' => 'nullable|file|mimes:jpeg,jpg,png,webp,pdf|max:10240',
            'duties' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'taxes' => 'nullable|numeric|min:0',
            'transportation_cost' => 'nullable|numeric|min:0',
            'demurrage' => 'nullable|numeric|min:0',
            // Client PDF 9/24, items 2-3: decides what a short receipt does.
            'shipment_type' => 'required|in:truck,container',
            'items' => 'required|array',
            // No upper bound: a shipment can arrive over or under the
            // originally ordered quantity (client feedback 9/21, items 1-2).
            'items.*.receive_qty' => 'nullable|integer|min:0',
            'items.*.ordered_qty' => 'nullable|integer|min:0',
        ];
    }
}
