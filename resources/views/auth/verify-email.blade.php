<x-guest-layout title="Verify Email | Warehouse POS">
    <div class="container vh-100 d-flex align-items-center justify-content-center">
        <div class="col-md-5 text-center">

            <h4 class="text-dark mb-2">Verify Your Email</h4>
            <p class="text-muted fs-14">
                Please verify your warehouse account email address.
            </p>

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button class="login-btn">Resend Verification Email</button>
            </form>

        </div>
    </div>
</x-guest-layout>
