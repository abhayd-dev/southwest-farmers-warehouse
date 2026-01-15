<x-app-layout title="My Profile">

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="mb-4">
        <h4 class="mb-0 fw-bold">My Profile</h4>
    </div>

    {{-- PROFILE CARD --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex align-items-center">
            {{-- UPDATED: Real Image Logic --}}
            <img src="{{ Auth::user()->profile_image ? asset(Auth::user()->profile_image) : asset('assets/images/default-avatar.png') }}"
                 alt="{{ $user->name }}"
                 class="rounded-circle me-3 border shadow-sm"
                 width="80" height="80"
                 style="object-fit: cover;">

            <div>
                {{-- UPDATED: text-dark instead of stats-number-red --}}
                <h4 class="mb-0 text-dark fw-bold">{{ $user->name }}</h4>
                <p class="text-muted mb-0">{{ $user->email }}</p>
                <div class="small text-muted mt-1">
                    Designation: <span class="fw-semibold">{{ $user->designation ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        {{-- PERSONAL INFO --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark">Personal Information</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label fw-bold">Name</label>
                            <input type="text" name="name"
                                   value="{{ old('name', $user->name) }}"
                                   class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Contact Phone</label>
                            <input type="text" name="phone"
                                   value="{{ old('phone', $user->phone) }}"
                                   class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email"
                                   value="{{ old('email', $user->email) }}"
                                   class="form-control" readonly style="background-color: #e9ecef;">
                                   <small class="text-muted">Email cannot be changed.</small>
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
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark">Change Password</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label fw-bold">Old Password</label>
                            <input type="password" name="current_password"
                                   class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">New Password</label>
                            <input type="password" name="password"
                                   class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Confirm Password</label>
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