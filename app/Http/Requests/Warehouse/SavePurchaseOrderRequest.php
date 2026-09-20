<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;

/** Rules shared by creating and editing a Purchase Order. */
class SavePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Access is enforced by the route permission rules.
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => 'required|exists:vendors,id',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost' => 'required|numeric|min:0',
            'approval_email' => 'nullable|email',
            'approver_phone' => 'nullable|string|max:20',
        ];
    }
}
