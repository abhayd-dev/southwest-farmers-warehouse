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

            @if (session('error'))
                Toast.fire({
                    icon: 'error',
                    title: @json(session('error'))
                });
            @endif

            @if ($errors->any())
                Toast.fire({
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
        $.fn.dataTable.ext.errMode = function(settings, helpPage, message) {
            console.warn('DataTables Ajax Error:', message);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Load Issue',
                    text: 'Something went wrong.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000
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

</body>

</html>
