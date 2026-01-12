@push('scripts')
    <script>
        const fetchSubcategoriesUrl =
            "{{ route('warehouse.product-options.fetch-subcategories', ':id') }}";

        $('#categorySelect').on('change', function() {
            let id = $(this).val();
            $('#subcategorySelect').html('<option>Loading...</option>');
            if (!id) return;

            $.get(fetchSubcategoriesUrl.replace(':id', id), function(data) {
                let html = '<option value="">Select</option>';
                data.forEach(i => html += `<option value="${i.id}">${i.name}</option>`);
                $('#subcategorySelect').html(html);
            });
        });
        $('.status-toggle').on('change', function() {
            let checkbox = $(this);
            let id = checkbox.data('id');
            let status = checkbox.is(':checked') ? 1 : 0;

            // revert UI immediately
            checkbox.prop('checked', !status);

            Swal.fire({
                title: 'Are you sure?',
                text: 'Change option status?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, change it',
            }).then(result => {
                if (result.isConfirmed) {

                    $.ajax({
                        url: "{{ route('warehouse.product-options.status') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id,
                            status: status
                        },
                        success: function(response) {

                            checkbox.prop('checked', status);

                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });

                            Toast.fire({
                                icon: 'success',
                                title: response.message ?? 'Status updated successfully'
                            });
                        },
                        error: function() {

                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });

                            Toast.fire({
                                icon: 'error',
                                title: 'Something went wrong!'
                            });
                        }
                    });

                }
            });
        });
    </script>
@endpush
