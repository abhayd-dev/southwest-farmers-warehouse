@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    /* ===============================
       HELPER: FETCH SUBCATEGORIES
    =============================== */
    const fetchSubcategories = (categoryId, targetSelectId, selectedSubId = null) => {
        const target = document.getElementById(targetSelectId);
        if (!target) return Promise.resolve();

        target.innerHTML = '<option value="">Loading...</option>';
        target.disabled = true;

        if (!categoryId) {
            target.innerHTML = '<option value="">Select Subcategory</option>';
            target.disabled = false;
            return Promise.resolve();
        }

        const url = "{{ url('warehouse/fetch-subcategories') }}/" + categoryId;

        return fetch(url)
            .then(res => res.json())
            .then(data => {
                target.innerHTML = '<option value="">Select Subcategory</option>';
                data.forEach(sub => {
                    const isSelected = selectedSubId && selectedSubId == sub.id ? 'selected' : '';
                    target.innerHTML += `<option value="${sub.id}" ${isSelected}>${sub.name}</option>`;
                });
                target.disabled = false;
            })
            .catch(err => {
                console.error('Error fetching subcategories:', err);
                target.innerHTML = '<option value="">Error loading</option>';
                target.disabled = false;
            });
    };

    /* ===============================
       DEPENDENT DROPDOWN: MAIN FORM
    =============================== */
    const catSelect = document.getElementById('categorySelect');
    if (catSelect) {
        catSelect.addEventListener('change', function() {
            fetchSubcategories(this.value, 'subcategorySelect');
        });
    }

    /* ===============================
       DEPENDENT DROPDOWN: IMPORT MODAL
    =============================== */
    const catSelectModal = document.getElementById('categorySelectModal');
    if (catSelectModal) {
        catSelectModal.addEventListener('change', function() {
            fetchSubcategories(this.value, 'subcategorySelectModal');
        });
    }

    /* ===============================
       SAFE INPUT SETTER
    =============================== */
    const setValue = (selector, value) => {
        const el = document.querySelector(selector);
        if (el) el.value = value ?? '';
    };

    /* ===============================
       PRODUCT OPTION AUTO FILL
    =============================== */
    const optionSelect = document.getElementById('productOptionSelect');
    if (optionSelect) {
        optionSelect.addEventListener('change', function() {
            const id = this.value;
            if (!id) return;

            const url = "{{ route('warehouse.products.fetch-option', ':id') }}".replace(':id', id);

            fetch(url)
                .then(res => res.json())
                .then(o => {
                    if (!o) return;

                    setValue('[name=product_name]', o.option_name);
                    setValue('[name=sku]', o.sku);
                    setValue('[name=unit]', o.unit);
                    setValue('[name=price]', o.base_price);
                    setValue('[name=barcode]', o.barcode);

                    if (o.category_id && catSelect) {
                        catSelect.value = o.category_id;
                        fetchSubcategories(o.category_id, 'subcategorySelect', o.subcategory_id);
                    }
                })
                .catch(err => {
                    console.error('Failed to fetch product option', err);
                });
        });
    }

    /* ===============================
       STATUS TOGGLE (AJAX + CONFIRM)
    =============================== */
    document.querySelectorAll('.status-toggle').forEach(function(checkbox) {
        checkbox.addEventListener('change', function(e) {
            e.preventDefault(); // Stop immediate toggle

            const id = this.dataset.id;
            const newStatus = this.checked ? 1 : 0;
            const originalState = !this.checked; 

            Swal.fire({
                title: 'Are you sure?',
                text: 'Change product status?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, change it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("{{ route('warehouse.products.status') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: id,
                            status: newStatus
                        })
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        }).fire({
                            icon: 'success',
                            title: data.message ?? 'Status updated successfully'
                        });
                        checkbox.checked = newStatus === 1; // Manually update UI
                    })
                    .catch(error => {
                        checkbox.checked = originalState; // Revert UI
                        Swal.fire('Error!', 'Failed to update status.', 'error');
                    });
                } else {
                    checkbox.checked = originalState; // Revert UI
                }
            });
        });
    });

    /* ===============================
       IMPORT MODAL CLEANUP
       (Only handling reset on close, NOT opening)
    =============================== */
    const importModalEl = document.getElementById('importModal');
    if (importModalEl) {
        importModalEl.addEventListener('hidden.bs.modal', function () {
            const form = this.querySelector('form');
            if (form) {
                form.reset();
                form.classList.remove('was-validated');
                
                // Clear the dependent dropdown
                const subSelect = document.getElementById('subcategorySelectModal');
                if (subSelect) subSelect.innerHTML = '<option value="">Select Subcategory</option>';
            }
        });
    }

});
</script>
@endpush