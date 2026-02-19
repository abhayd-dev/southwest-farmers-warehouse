@component('mail::message')
# New Store Order Created

Hello,

A new Purchase Order **#{{ $po->po_number }}** has been automatically generated for **{{ $po->store->store_name }}**.

**Order Details:**
- **Date:** {{ $po->request_date->format('d M Y') }}
- **Total Items:** {{ $po->items->count() }}

Please review the order and approve it.

@component('mail::button', ['url' => route('warehouse.store-orders.show', $po->id)])
View Order
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
