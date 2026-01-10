<x-guest-layout title="Register | Warehouse POS">
    <div class="account-page">
        <div class="container-fluid p-0">
            <div class="row align-items-center justify-content-center vh-100">

                <div class="col-xl-4 col-md-6">

                    <div class="auth-title-section text-center mb-3">
                        <h3 class="text-dark">Create Warehouse Account</h3>
                        <p class="text-muted fs-14">Register new warehouse user</p>
                    </div>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-3">
                            <label>Name</label>
                            <input class="form-control" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label>Email</label>
                            <input class="form-control" type="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label>Password</label>
                            <input class="form-control" type="password" name="password" required>
                        </div>

                        <div class="mb-3">
                            <label>Confirm Password</label>
                            <input class="form-control" type="password" name="password_confirmation" required>
                        </div>

                        <div class="d-grid">
                            <button class="login-btn">Register</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
