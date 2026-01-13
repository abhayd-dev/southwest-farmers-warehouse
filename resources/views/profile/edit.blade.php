<x-app-layout title="My Profile">

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="mb-4">
        <h4 class="mb-0">My Profile</h4>
    </div>

    {{-- PROFILE CARD --}}
    <div class="card mb-4">
        <div class="card-body d-flex align-items-center">
            <img src="{{ asset('assets/images/users/profile.jpg') }}"
                 class="rounded-circle me-3"
                 width="80" height="80">

            <div>
                <h4 class="mb-0 stats-number-red">{{ $user->name }}</h4>
                <p class="text-muted mb-0">{{ $user->email }}</p>
            </div>
        </div>
    </div>

    <div class="row">

        {{-- PERSONAL INFO --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Personal Information</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name"
                                   value="{{ old('name', $user->name) }}"
                                   class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" name="phone"
                                   value="{{ old('phone', $user->phone) }}"
                                   class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email"
                                   value="{{ old('email', $user->email) }}"
                                   class="form-control">
                        </div>

                        <button class="btn btn-primary w-100">
                            Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- CHANGE PASSWORD --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Change Password</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Old Password</label>
                            <input type="password" name="current_password"
                                   class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password"
                                   class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation"
                                   class="form-control">
                        </div>

                        <button class="btn btn-primary w-100">
                            Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>

</x-app-layout>
