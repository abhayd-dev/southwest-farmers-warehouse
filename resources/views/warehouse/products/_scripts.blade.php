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
            if (barcodeInput) {
                if (upcInput && !barcodeInput.value) {
                    barcodeInput.value = upcInput.value;
                }
                renderBarcode(barcodeInput.value);
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const catSelect = document.getElementById('categorySelect');
            const detailsCard = document.getElementById('productDetailsCard');
            const toggleManualBtn = document.getElementById('toggleManualEntry');
            const optionSelect = document.getElementById('productOptionSelect');

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

                const url = "{{ route('warehouse.product-options.fetch-subcategories', ':id') }}".replace(':id', categoryId);

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
                        if (window.jQuery && $(target).data('select2')) {
                            $(target).trigger('change');
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching subcategories:', err);
                        target.innerHTML = '<option value="">Error loading</option>';
                        target.disabled = false;
                    });
            };

            // Use jQuery for event listeners to ensure compatibility with Select2
            $(document).on('change', '#categorySelect', function() {
                fetchSubcategories(this.value, 'subcategorySelect');
            });

            $(document).on('change', '#categorySelectModal', function() {
                fetchSubcategories(this.value, 'subcategorySelectModal');
            });

            const setValue = (selector, value) => {
                const el = document.querySelector(selector);
                if (el) {
                    el.value = value ?? '';
                    if (window.jQuery && $(el).data('select2')) {
                        $(el).trigger('change');
                    }
                }
            };

            $(document).on('change', '#productOptionSelect', function() {
                const id = this.value;
                if (!id) {
                    if (detailsCard) detailsCard.style.display = 'none';
                    return;
                }

                if (detailsCard) detailsCard.style.display = 'block';

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

                        if (o.category_id) {
                            setValue('#categorySelect', o.category_id);
                            fetchSubcategories(o.category_id, 'subcategorySelect', o.subcategory_id);
                        }
                    })
                    .catch(err => {
                        console.error('Failed to fetch product option', err);
                    });
            });

            if (toggleManualBtn) {
                toggleManualBtn.addEventListener('click', function() {
                    if (optionSelect) optionSelect.value = "";
                    if (detailsCard) detailsCard.style.display = 'block';

                    setValue('[name=product_name]', '');
                    setValue('[name=sku]', '');
                    setValue('[name=unit]', '');
                    setValue('[name=price]', '');
                    setValue('[name=barcode]', '');

                    setValue('#categorySelect', '');
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

            // Pricing Calculations
            const warehouseMarkup = document.getElementById('warehouseMarkupInput');
            const costPrice = document.getElementById('costPriceInput');
            const warehousePrice = document.getElementById('warehousePriceInput');
            const storeMarkup = document.getElementById('storeMarkupInput');
            const storeRetailPrice = document.getElementById('storeRetailPriceInput');

            function calculateWarehousePrice() {
                const markup = parseFloat(warehouseMarkup.value) || 0;
                const cost = parseFloat(costPrice.value) || 0;
                const price = cost * (1 + markup / 100);
                warehousePrice.value = price.toFixed(2);
                calculateStorePrice();
            }

            function calculateStorePrice() {
                const markup = parseFloat(storeMarkup.value) || 0;
                const whPrice = parseFloat(warehousePrice.value) || 0;
                const retailPrice = whPrice * (1 + markup / 100);
                storeRetailPrice.value = retailPrice.toFixed(2);
            }

            if (warehouseMarkup && costPrice) {
                warehouseMarkup.addEventListener('input', calculateWarehousePrice);
                costPrice.addEventListener('input', calculateWarehousePrice);
            }

            if (storeMarkup && warehousePrice) {
                storeMarkup.addEventListener('input', calculateStorePrice);
                warehousePrice.addEventListener('input', calculateStorePrice);
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
