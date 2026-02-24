@props([
    'viewUrl' => null,
    'editUrl' => null,
    'deleteUrl' => null,
    'data' => null,
])

<div class="action-btns">
    {{ $slot }}
    @isset($viewUrl)
        @if($viewUrl || $data)
            <a href="{{ $viewUrl ?? 'javascript:void(0)' }}" 
               class="btn btn-sm btn-outline-info btn-view {{ $data ? 'view-btn' : '' }}" 
               @if($data) data-data="{{ is_string($data) ? $data : json_encode($data) }}" @endif
               title="View">
                <i class="mdi mdi-eye"></i>
            </a>
        @endif
    @endisset

    @if($editUrl)
        <a href="{{ $editUrl }}" class="btn btn-sm btn-outline-primary btn-edit" title="Edit">
            <i class="mdi mdi-pencil"></i>
        </a>
    @endif

    @if($deleteUrl)
        <form action="{{ $deleteUrl }}" method="POST" class="d-inline delete-form">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger btn-delete" title="Delete">
                <i class="mdi mdi-delete"></i>
            </button>
        </form>
    @endif
</div>
