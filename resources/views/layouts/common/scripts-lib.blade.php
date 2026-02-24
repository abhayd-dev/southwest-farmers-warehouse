<!-- jQuery (required by DataTables, Bootstrap, etc.) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Iconify -->
<script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

<!-- Simplebar -->
<script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>

<!-- Waves -->
<script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>

<!-- Waypoints + CounterUp -->
<script src="{{ asset('assets/libs/waypoints/lib/jquery.waypoints.min.js') }}"></script>
<script src="{{ asset('assets/libs/jquery.counterup/jquery.counterup.min.js') }}"></script>

<!-- Feather Icons -->
<script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables JS (Core + Bootstrap 5 + Buttons + Export) -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- ApexCharts (for valuation chart) -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Global Initializations -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Flatpickr on all inputs of type date, or inputs with class .datepicker or .flatpickr
        flatpickr("input[type='date'], .datepicker, .flatpickr", {
            dateFormat: "Y-m-d",
            allowInput: true
        });

        // Initialize Select2 on all select elements unless they have the class .no-select2
        $(document).ready(function() {
            $('select:not(.no-select2, .swal2-select, [class^="swal2-"])').each(function() {
                $(this).select2({
                    theme: 'bootstrap-5',
                    width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                    placeholder: $(this).data('placeholder'),
                    allowClear: Boolean($(this).data('placeholder'))
                });
            });
            
            // Fix Select2 search focus within Bootstrap Modals
            $(document).on('shown.bs.modal', function (e) {
                $(this).find('select').each(function() {
                    let dropdownParent = $(e.target);
                    // Re-initialize for modal
                    $(this).select2({
                        theme: 'bootstrap-5',
                        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                        dropdownParent: dropdownParent,
                        placeholder: $(this).data('placeholder'),
                        allowClear: Boolean($(this).data('placeholder'))
                    });
                });
            });
        });
    });
</script>

<script>
    /**
     * Universal Image Error Handler
     * Automatically replaces broken images with a local professional placeholder.
     */
    document.addEventListener('error', function (event) {
        if (event.target.tagName.toLowerCase() !== 'img') return;
        
        const img = event.target;
        const placeholder = "{{ asset('assets/images/placeholder.svg') }}";
        
        // Prevent infinite loops if the placeholder itself is missing
        if (img.src === placeholder) return;

        // Use custom placeholder if provided via data-placeholder attribute
        const customPlaceholder = img.getAttribute('data-placeholder');
        img.src = customPlaceholder ? customPlaceholder : placeholder;
        
        // Add a class for optional styling
        img.classList.add('img-placeholder');
    }, true);
</script>

<!-- Your custom app.js (keep at the end) -->
<script src="{{ asset('assets/js/app.js') }}"></script>

<!-- Head.js (if needed - usually for modernizr/polyfills) -->
<script src="{{ asset('assets/js/head.js') }}"></script>