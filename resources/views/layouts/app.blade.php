@props(['title' => 'Warehouse Admin'])
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>{{ $title }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Warehouse Admin Panel" name="description" />

    @include('layouts.common.styles-lib')
    @stack('styles-lib')
    @stack('styles')
    <style>
        /* Consistent page-title breadcrumb styling (item 14): blue link,
           bold + underline on hover, matching the sidebar navigation text. */
        .page-breadcrumb-link { color: #0d6efd; text-decoration: none; }
        .page-breadcrumb-link:hover { color: #0d6efd; font-weight: 700; text-decoration: underline; }
    </style>
    <style>
        .swal2-popup.swal2-toast.error-toast-wide { width: min(560px, 92vw) !important; }
        .error-toast-wide .swal2-title { font-size: 0.9rem !important; user-select: text; word-break: break-word; }
    </style>
</head>

<body data-menu-color="light" data-sidebar="default">

    <div id="app-layout">

        @include('layouts.partials.header')

        @include('layouts.partials.sidebar')

        <div class="content-page">
            <div class="content">

                {{ $slot ?? '' }}
                @yield('content')

            </div>

            @include('layouts.partials.footer')

        </div>
    </div>

    @include('layouts.common.scripts-lib')
    @stack('scripts-lib')

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const forms = document.querySelectorAll('.needs-validation');

            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {

                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }

                    form.classList.add('was-validated');

                }, false);
            });

        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                showCloseButton: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            @if (session('success'))
                Toast.fire({
                    icon: 'success',
                    title: @json(session('success'))
                });
            @endif

            // Errors stay until closed (and can be selected/copied): with real
            // errors on, the message is worth reading.
            const ErrorToast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                showCloseButton: true,
                customClass: { popup: 'error-toast-wide' },
            });

            @if (session('error'))
                ErrorToast.fire({
                    icon: 'error',
                    title: @json(session('error'))
                });
            @endif

            @if ($errors->any())
                ErrorToast.fire({
                    icon: 'error',
                    title: @json($errors->first())
                });
            @endif
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.body.addEventListener('submit', function(e) {
                if (e.target.classList.contains('delete-form')) {
                    e.preventDefault();

                    const form = e.target;

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                }
            });
        });
    </script>
    <script>
        /**
         * The real reason a request failed, from a jQuery xhr, a fetch()
         * JSON body, or an Error -- the server sends the real error to
         * warehouse staff while SHOW_REAL_ERRORS is on. Falls back to
         * `fallback` when there is nothing better.
         */
        window.serverErrorMessage = function (source, fallback) {
            fallback = fallback || 'Something went wrong. Please try again later.';
            try {
                if (!source) return fallback;
                if (source.serverMessage) return source.serverMessage; // from jsonOrThrow()
                const json = source.responseJSON || (source.message !== undefined && source.success !== undefined ? source : null);
                if (json) {
                    if (json.errors && typeof json.errors === 'object') {
                        const first = Object.values(json.errors)[0];
                        if (first) return Array.isArray(first) ? first[0] : String(first);
                    }
                    if (json.message) return json.message;
                }
                if (source.status === 0) return 'Could not reach the server (network error or timeout).';
                if (source.status) return fallback + ' (HTTP ' + source.status + (source.statusText ? ' ' + source.statusText : '') + ')';
                if (source instanceof Error && source.message) return fallback + ' (' + source.message + ')';
            } catch (e) {}
            return fallback;
        };

        /**
         * For fetch(): .then(jsonOrThrow) -- a failed response (HTTP error or
         * {success: false}) becomes a rejected promise whose error carries the
         * server's message, instead of being treated as a success.
         */
        window.jsonOrThrow = function (res) {
            return res.json().catch(() => ({})).then(data => {
                if (!res.ok || (data && data.success === false)) {
                    let msg = data && data.message;
                    if (data && data.errors && typeof data.errors === 'object') {
                        const first = Object.values(data.errors)[0];
                        if (first) msg = Array.isArray(first) ? first[0] : String(first);
                    }
                    const err = new Error(msg || ('Request failed (HTTP ' + res.status + ')'));
                    err.serverMessage = err.message;
                    throw err;
                }
                return data;
            });
        };

        $.fn.dataTable.ext.errMode = function(settings, helpPage, message) {
            console.warn('DataTables Ajax Error:', message);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Load Issue',
                    text: serverErrorMessage(settings && settings.jqXHR, 'Something went wrong while loading this table.'),
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 8000
                });
            }
        };
    </script>
    <script>
        // Robust Connection Monitoring
        (function() {
            function showConnectionToast(isOnline) {
                if (typeof Swal === 'undefined') return;

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                if (isOnline) {
                    Toast.fire({
                        icon: 'success',
                        title: 'Connection Restored',
                        text: 'You are back online.'
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Connection Lost',
                        text: 'Please check your internet. You can continue working, but changes may not save until restored.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 6000,
                        timerProgressBar: true
                    });
                }
            }

            window.addEventListener('offline', function() {
                showConnectionToast(false);
            });

            window.addEventListener('online', function() {
                showConnectionToast(true);
            });
        })();
    </script>
    <script>
        // Custom Sidebar Responsive Logic
        (function() {
            function adjustSidebar() {
                const body = document.body;
                const width = window.innerWidth;
                
                // User requirement: 
                // Phones & Mini Tabs (< 992px) -> Closed (hidden)
                // Tablets, Laptops, Desktops (>= 992px) -> Open (default)
                if (width < 992) {
                    body.setAttribute('data-sidebar', 'hidden');
                } else {
                    body.setAttribute('data-sidebar', 'default');
                }
            }

            // Initial adjustment
            adjustSidebar();

            // Handle resize
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(adjustSidebar, 100);
            });

            // Re-run after main app.js loads just in case
            window.addEventListener('load', adjustSidebar);
        })();
    </script>
    @stack('scripts')
    @include('layouts.partials._import-progress-scripts')

</body>

</html>
