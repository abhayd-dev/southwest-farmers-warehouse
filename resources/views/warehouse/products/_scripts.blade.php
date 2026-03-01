@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const barcodeInput = document.getElementById('barcodeInput');
            const generateBtn = document.getElementById('generateBarcodeBtn');

            function renderBarcode(value) {
                if (value) {
                    try {
                        JsBarcode("#barcodeDisplay", value, {
                            format: "CODE128",
                            lineColor: "#000",
                            width: 2,
                            height: 40,
                            displayValue: false
                        });
                    } catch (e) {
                        console.error("Invalid barcode format");
                        document.getElementById('barcodeDisplay').style.display = 'none';
                    }
                    document.getElementById('barcodeDisplay').style.display = 'block';
                } else {
                    document.getElementById('barcodeDisplay').style.display = 'none';
                }
            }

            const upcInput = document.getElementById('upcInput');
            const generateUpcBtn = document.getElementById('generateUpcBtn');

            if (generateUpcBtn) {
                generateUpcBtn.addEventListener('click', function() {
                    fetch("{{ route('warehouse.products.generate-upc') }}")
                        .then(response => response.json())
                        .then(data => {
                            if (upcInput) {
                                upcInput.value = data.upc;
                                // Dispatch input event so barcode syncs
                                upcInput.dispatchEvent(new Event('input'));
                            }
                        })
                        .catch(err => console.error(err));
                });
            }

            // Sync Barcode with UPC
            if (upcInput) {
                upcInput.addEventListener('input', function() {
                    const val = this.value.trim();
                    barcodeInput.value = val;
                    renderBarcode(val);
                });
            }

            // Initial Render
            if (upcInput && !barcodeInput.value) {
                barcodeInput.value = upcInput.value;
            }
            renderBarcode(barcodeInput.value);
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

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
                            const isSelected = selectedSubId && selectedSubId == sub.id ?
                                'selected' : '';
                            target.innerHTML +=
                                `<option value="${sub.id}" ${isSelected}>${sub.name}</option>`;
                        });
                        target.disabled = false;
                    })
                    .catch(err => {
                        console.error('Error fetching subcategories:', err);
                        target.innerHTML = '<option value="">Error loading</option>';
                        target.disabled = false;
                    });
            };

            const catSelect = document.getElementById('categorySelect');
            if (catSelect) {
                catSelect.addEventListener('change', function() {
                    fetchSubcategories(this.value, 'subcategorySelect');
                });
            }

            const catSelectModal = document.getElementById('categorySelectModal');
            if (catSelectModal) {
                catSelectModal.addEventListener('change', function() {
                    fetchSubcategories(this.value, 'subcategorySelectModal');
                });
            }

            const setValue = (selector, value) => {
                const el = document.querySelector(selector);
                if (el) el.value = value ?? '';
            };

            const optionSelect = document.getElementById('productOptionSelect');
            const detailsCard = document.getElementById('productDetailsCard');
            const toggleManualBtn = document.getElementById('toggleManualEntry');

            if (optionSelect) {
                optionSelect.addEventListener('change', function() {
                    const id = this.value;
                    if (!id) {
                        detailsCard.style.display = 'none';
                        return;
                    }

                    detailsCard.style.display = 'block';

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
                                fetchSubcategories(o.category_id, 'subcategorySelect', o
                                    .subcategory_id);
                            }
                        })
                        .catch(err => {
                            console.error('Failed to fetch product option', err);
                        });
                });
            }

            if (toggleManualBtn) {
                toggleManualBtn.addEventListener('click', function() {
                    optionSelect.value = "";
                    detailsCard.style.display = 'block';

                    setValue('[name=product_name]', '');
                    setValue('[name=sku]', '');
                    setValue('[name=unit]', '');
                    setValue('[name=price]', '');
                    setValue('[name=barcode]', '');

                    if (catSelect) catSelect.value = "";
                    const subSelect = document.getElementById('subcategorySelect');
                    if (subSelect) subSelect.innerHTML = '<option value="">Select Subcategory</option>';
                });
            }

            const existingProductId = "{{ isset($product) ? $product->id : '' }}";
            const existingOptionId = "{{ isset($product) ? $product->product_option_id : '' }}";

            if (existingProductId || existingOptionId) {
                if (detailsCard) detailsCard.style.display = 'block';
            }

            document.querySelectorAll('.status-toggle').forEach(function(checkbox) {
                checkbox.addEventListener('change', function(e) {
                    e.preventDefault();

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
                                    if (!response.ok) throw new Error(
                                        'Network response was not ok');
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
                                        title: data.message ??
                                            'Status updated successfully'
                                    });
                                    checkbox.checked = newStatus === 1;
                                })
                                .catch(error => {
                                    checkbox.checked = originalState;
                                    Swal.fire('Error!', 'Failed to update status.',
                                        'error');
                                });
                        } else {
                            checkbox.checked = originalState;
                        }
                    });
                });
            });

            const importModalEl = document.getElementById('importModal');
            if (importModalEl) {
                importModalEl.addEventListener('hidden.bs.modal', function() {
                    const form = this.querySelector('form');
                    if (form) {
                        form.reset();
                        form.classList.remove('was-validated');

                        const subSelect = document.getElementById('subcategorySelectModal');
                        if (subSelect) subSelect.innerHTML = '<option value="">Select Subcategory</option>';
                    }
                });
            }

        });

        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('iconPreview').src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
@endpush
