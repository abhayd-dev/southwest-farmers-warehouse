<x-guest-layout title="Login | Warehouse POS">
    <div class="account-page">
        <div class="container-fluid p-0">
            <div class="row align-items-center justify-content-center g-0 min-vh-100">

                <div class="col-12 col-xl-10 col-xxl-9">
                    <div class="row align-items-center g-0 px-3 px-md-4 py-4">

                        {{-- LOGO SECTION - LEFT SIDE --}}
                        <div class="col-12 col-lg-6 mb-5 mb-lg-0 pe-lg-5">
                            <div class="login-logo-section d-flex flex-column align-items-center align-items-lg-start justify-content-center h-100">
                                <div class="auth-brand mb-4">
                                    <a href="{{ url('/') }}" class="logo logo-light">
                                        <span class="logo-lg">
                                            <img src="{{ Storage::url($settings['login_logo']) }}"
                                                 alt="Warehouse POS"
                                                 height="40"
                                                 class="d-inline-block">
                                        </span>
                                    </a>

                                    <a href="{{ url('/') }}" class="logo logo-dark">
                                        <span class="logo-lg">
                                            <img src="{{ Storage::url($settings['main_logo']) }}"
                                                 alt="Warehouse POS"
                                                 height="40"
                                                 class="d-inline-block">
                                        </span>
                                    </a>
                                </div>

                                {{-- Optional: Add welcome text or illustration on left side --}}
                                <div class="text-center text-lg-start">
                                    <h2 class="fw-bold text-dark mb-3 d-none d-lg-block">Warehouse POS</h2>
                                    <p class="text-muted fs-15 d-none d-lg-block">
                                        Manage your warehouse operations efficiently with our comprehensive point of sale system.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- LOGIN FORM - RIGHT SIDE --}}
                        <div class="col-12 col-lg-6 ps-lg-5">
                            <div class="login-wrap">
                                <div class="auth-title-section mb-4 text-center text-lg-start">
                                    <h3 class="text-dark fw-semibold mb-2">
                                        Welcome Back
                                    </h3>
                                    <p class="text-muted fs-14 mb-0">
                                        Please sign in to continue to <strong>Warehouse POS</strong>.
                                    </p>
                                </div>

                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-4 p-md-5">
                                        <form method="POST"
                                              action="{{ route('login') }}"
                                              class="mt-0"
                                              data-form>
                                            @csrf

                                            {{-- EMAIL --}}
                                            <div class="form-group mb-3">
                                                <label for="email" class="form-label fw-medium">
                                                    Email Address
                                                </label>

                                                <input
                                                    class="form-control form-control-lg @error('email') is-invalid @enderror"
                                                    type="email"
                                                    name="email"
                                                    id="email"
                                                    value="{{ old('email') }}"
                                                    required
                                                    autofocus
                                                    placeholder="Enter your email">

                                                @error('email')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            {{-- PASSWORD --}}
                                            <div class="form-group mb-3">
                                                <label for="password" class="form-label fw-medium">
                                                    Password
                                                </label>

                                                <input
                                                    class="form-control form-control-lg @error('password') is-invalid @enderror"
                                                    type="password"
                                                    name="password"
                                                    id="password"
                                                    required
                                                    placeholder="Enter your password">

                                                @error('password')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            {{-- OPTIONS --}}
                                            <div class="form-group options d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4">
                                                <div class="mb-2 mb-sm-0">
                                                    <div class="form-check">
                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input"
                                                            id="remember_me"
                                                            name="remember">
                                                        <label class="form-check-label" for="remember_me">
                                                            Remember me
                                                        </label>
                                                    </div>
                                                </div>

                                                <div>
                                                    @if (Route::has('password.request'))
                                                        <a class="text-primary text-decoration-none fs-14"
                                                           href="{{ route('password.request') }}">
                                                            Forgot password?
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- SUBMIT --}}
                                            <div class="form-group mb-0">
                                                <div class="d-grid">
                                                    <button class="btn btn-primary btn-lg login-btn" type="submit">
                                                        Log In
                                                    </button>
                                                </div>
                                            </div>

                                        </form>
                                    </div>
                                </div>

                                {{-- Optional: Additional links --}}
                                <div class="text-center text-muted mt-4">
                                    <small>© {{ date('Y') }} Warehouse POS. All rights reserved.</small>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Add custom styles --}}
    <style>
        .account-page {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .login-logo-section {
            min-height: 300px;
        }

        .login-btn {
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .card {
            border-radius: 12px;
            overflow: hidden;
        }

        .form-control-lg {
            padding: 12px 16px;
            font-size: 15px;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        @media (max-width: 991.98px) {
            .login-logo-section {
                min-height: auto;
                padding-bottom: 2rem;
            }
        }

        @media (max-width: 575.98px) {
            .card-body {
                padding: 1.5rem !important;
            }
        }
    </style>
</x-guest-layout>