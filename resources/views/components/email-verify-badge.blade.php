@props(['verified', 'type', 'id', 'label'])

<span class="d-inline-flex align-items-center gap-2">
    @if ($verified)
        <span class="badge bg-success-subtle text-success">
            <i class="mdi mdi-check-decagram me-1"></i> Verified
        </span>
    @else
        <span class="badge bg-warning-subtle text-warning">
            <i class="mdi mdi-clock-alert-outline me-1"></i> Pending verification
        </span>
        <form action="{{ route('warehouse.contact-email.resend') }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="id" value="{{ $id }}">
            <input type="hidden" name="label" value="{{ $label }}">
            <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-underline">Resend</button>
        </form>
    @endif
</span>
