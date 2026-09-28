@component('mail::message')
# Store Order Needs Attention

Hello,

Store order **#{{ $po->po_number }}** from **{{ $po->store->store_name ?? 'a store' }}** has not been completed yet.

**Order Details:**
- **Status:** {{ ucfirst(str_replace('_', ' ', $po->status)) }}
- **Created:** {{ $po->created_at?->displayTime()->format('d M Y, h:i A') }}
- **Total Items:** {{ $po->items->count() }}

Please review it so the store receives its stock on time.

@component('mail::button', ['url' => route('warehouse.store-orders.show', $po->id)])
View Order
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
