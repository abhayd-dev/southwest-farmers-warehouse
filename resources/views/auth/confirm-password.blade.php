<x-guest-layout title="Confirm Password | Warehouse POS">
    <div class="container vh-100 d-flex align-items-center justify-content-center">

        <div class="col-md-4">
            <h4 class="text-dark">Confirm Password</h4>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div class="mb-3">
                    <label>Password</label>
                    <input class="form-control" type="password" name="password" required>
                </div>

                <button class="login-btn w-100">Confirm</button>
            </form>
        </div>

    </div>
</x-guest-layout>
