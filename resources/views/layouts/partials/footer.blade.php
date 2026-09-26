<footer class="footer">
    <div class="container-fluid">
        <div class="row align-items-center">
            
            {{-- Left Side - Copyright --}}
            <div class="col-12 text-center text-md-start">
                <span class="fs-13 text-muted">
                    &copy; {{ date('Y') }} 
                    <a href="{{ url('/') }}" class="text-reset fw-semibold text-decoration-none">
                        Warehouse POS
                    </a>
                    . All rights reserved.
                </span>
            </div>

        </div>

        {{-- Optional: Version Info (can be shown/hidden based on settings) --}}
        {{-- <div class="row mt-2">
            <div class="col-12 text-center">
                <small class="text-muted fs-12">Version 1.0.0 | Build {{ config('app.build_number', '2026.01') }}</small>
            </div>
        </div> --}}
    </div>

    <style>
        .footer {
            padding: 1rem 0;
            background: #fff;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        .hover-primary:hover {
            color: var(--bs-primary) !important;
            transition: color 0.3s ease;
        }

        @media (max-width: 767.98px) {
            .footer {
                padding: 0.75rem 0;
            }
            
            .footer .fs-13 {
                font-size: 12px !important;
            }
        }
    </style>
</footer>