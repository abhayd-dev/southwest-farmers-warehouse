<x-guest-layout title="Reset Password | Warehouse POS">
    <div class="account-page">
        <div class="container-fluid">
            <div class="row justify-content-center vh-100 align-items-center">

                <div class="col-md-4">

                    <h4 class="text-dark mb-2">Reset Password</h4>

                    <form method="POST" action="{{ route('password.store') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <div class="mb-3">
                            <label>Email</label>
                            <input class="form-control" type="email" name="email"
                                   value="{{ old('email', $request->email) }}" required>
                        </div>

                        <div class="mb-3">
                            <label>New Password</label>
                            <input class="form-control" type="password" name="password" required>
                        </div>

                        <div class="mb-3">
                            <label>Confirm Password</label>
                            <input class="form-control" type="password" name="password_confirmation" required>
                        </div>

                        <button class="login-btn w-100">Reset Password</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
