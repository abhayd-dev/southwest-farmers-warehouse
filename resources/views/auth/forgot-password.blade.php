<x-guest-layout title="Forgot Password | Warehouse POS">
    <div class="account-page">
        <div class="container-fluid p-0">
            <div class="row align-items-center justify-content-center g-0 min-vh-100">

                <div class="col-12 col-xl-10 col-xxl-9">
                    <div class="row align-items-center g-0 px-3 px-md-4 py-4">

                        {{-- LEFT SIDE - CONTENT SECTION --}}
                        <div class="col-12 col-lg-6 mb-5 mb-lg-0 pe-lg-5">
                            <div class="content-section d-flex flex-column align-items-center align-items-lg-start justify-content-center h-100">
                                
                                {{-- Logo --}}
                                <div class="auth-brand mb-4">
                                    <a href="{{ url('/') }}" class="logo">
                                        <img src="{{ asset('assets/images/logo.jpg') }}"
                                             alt="Warehouse POS"
                                             height="40"
                                             class="d-inline-block">
                                    </a>
                                </div>

                                {{-- Illustration/Icon --}}
                                <div class="mb-4 text-center text-lg-start">
                                    <div class="icon-wrapper mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                            <circle cx="12" cy="16" r="1"></circle>
                                        </svg>
                                    </div>
                                </div>

                                {{-- Content --}}
                                <div class="text-center text-lg-start">
                                    <h2 class="fw-bold text-dark mb-3">Password Recovery</h2>
                                    <p class="text-muted fs-15 mb-4 lh-base">
                                        Don't worry! It happens to the best of us. Enter your email address associated with your Warehouse POS account, and we'll send you a secure link to reset your password.
                                    </p>
                                    
                                    <div class="info-box p-4 bg-light rounded-3 d-none d-lg-block">
                                        <h6 class="fw-semibold text-dark mb-3">Security Tips:</h6>
                                        <ul class="text-muted small mb-0 ps-3">
                                            <li class="mb-2">The reset link will expire in 60 minutes</li>
                                            <li class="mb-2">Make sure to check your spam folder</li>
                                            <li class="mb-2">Use a strong, unique password</li>
                                            <li class="mb-0">Never share your password with anyone</li>
                                        </ul>
                                    </div>

                                    <div class="mt-4 d-none d-lg-block">
                                        <a href="{{ route('login') }}" class="text-decoration-none text-primary fw-medium">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                                <polyline points="12 19 5 12 12 5"></polyline>
                                            </svg>
                                            Back to Login
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT SIDE - FORM SECTION --}}
                        <div class="col-12 col-lg-6 ps-lg-5">
                            <div class="form-wrap">
                                <div class="form-header mb-4 text-center text-lg-start">
                                    <h3 class="text-dark fw-semibold mb-2">
                                        Forgot Your Password?
                                    </h3>
                                    <p class="text-muted fs-14 mb-0">
                                        Enter your warehouse email to receive a reset link
                                    </p>
                                </div>

                                {{-- Session Status --}}
                                @if (session('status'))
                                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        {{ session('status') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-4 p-md-5">
                                        <form method="POST" action="{{ route('password.email') }}">
                                            @csrf

                                            {{-- Email Input --}}
                                            <div class="form-group mb-4">
                                                <label for="email" class="form-label fw-medium">
                                                    Email Address
                                                </label>

                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted">
                                                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                                            <polyline points="22,6 12,13 2,6"></polyline>
                                                        </svg>
                                                    </span>
                                                    <input
                                                        type="email"
                                                        class="form-control form-control-lg border-start-0 ps-0 @error('email') is-invalid @enderror"
                                                        id="email"
                                                        name="email"
                                                        value="{{ old('email') }}"
                                                        required
                                                        autofocus
                                                        placeholder="Enter your registered email">

                                                    @error('email')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                                <small class="text-muted mt-2 d-block">
                                                    We'll send you a secure link to reset your password
                                                </small>
                                            </div>

                                            {{-- Submit Button --}}
                                            <div class="form-group mb-0">
                                                <div class="d-grid">
                                                    <button type="submit" class="btn btn-primary btn-lg login-btn">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                                                            <line x1="22" y1="2" x2="11" y2="13"></line>
                                                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                                        </svg>
                                                        Send Reset Link
                                                    </button>
                                                </div>
                                            </div>

                                        </form>
                                    </div>
                                </div>

                                {{-- Back to Login Link (Mobile) --}}
                                <div class="text-center mt-4 d-lg-none">
                                    <a href="{{ route('login') }}" class="text-decoration-none text-primary fw-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                                            <line x1="19" y1="12" x2="5" y2="12"></line>
                                            <polyline points="12 19 5 12 12 5"></polyline>
                                        </svg>
                                        Back to Login
                                    </a>
                                </div>

                                {{-- Support Info --}}
                                <div class="text-center text-muted mt-4">
                                    <small>
                                        Need help? Contact 
                                        <a href="mailto:support@warehousepos.com" class="text-primary text-decoration-none">
                                            support@warehousepos.com
                                        </a>
                                    </small>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Custom Styles --}}
    <style>
        .account-page {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .content-section {
            min-height: 300px;
        }

        .icon-wrapper {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .card {
            border-radius: 12px;
            overflow: hidden;
        }

        .form-control-lg {
            padding: 12px 16px;
            font-size: 15px;
        }

        .input-group-text {
            border-right: 0;
        }

        .form-control:focus {
            border-color: #12ef4d;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .login-btn {
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .info-box {
            border-left: 4px solid #0dfd59;
        }

        .alert {
            display: flex;
            align-items: center;
        }

        @media (max-width: 991.98px) {
            .content-section {
                min-height: auto;
                padding-bottom: 2rem;
            }

            .icon-wrapper {
                display: flex;
                justify-content: center;
            }
        }

        @media (max-width: 575.98px) {
            .card-body {
                padding: 1.5rem !important;
            }

            .icon-wrapper svg {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</x-guest-layout>