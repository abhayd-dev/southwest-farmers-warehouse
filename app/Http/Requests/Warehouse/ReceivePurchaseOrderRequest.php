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
            // Client 9/27: nothing more is coming -- complete the order now even
            // if lines are short (the invoice is reduced to what was received).
            'complete_now' => 'nullable|boolean',
            'items' => 'required|array',
            // No upper bound: a shipment can arrive over or under the
            // originally ordered quantity (client feedback 9/21, items 1-2).
            // Decimals allowed (QA: e.g. produce received by weight).
            'items.*.receive_qty' => 'nullable|numeric|min:0',
            'items.*.ordered_qty' => 'nullable|numeric|min:0',
            // Client ticket 23: returnable pallet/divider counts received
            // alongside this shipment, credited to the vendor's balance.
            'pallets_received' => 'nullable|numeric|min:0',
            'dividers_received' => 'nullable|numeric|min:0',
        ];
    }
}
