@extends('layouts.app')

@section('content')
<div class="min-vh-100 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-primary text-white py-3 rounded-top">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-circle fa-2x me-3"></i>
                            <h4 class="mb-0">Profile Settings</h4>
                        </div>
                    </div>
                    <div class="card-body p-4 p-lg-5">
                        @if ($errors->any())
                            <div class="alert alert-danger rounded-3 shadow-sm">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('profile') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="name" name="name" 
                                            value="{{ old('name', $user->name) }}" required placeholder="Enter your name">
                                        <label for="name">Full Name <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email" 
                                            value="{{ old('email', $user->email) }}" required placeholder="Enter your email">
                                        <label for="email">Email Address <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                            </div>

                            <div class="position-relative my-4">
                                <hr class="text-muted">
                                <span class="position-absolute top-50 start-50 translate-middle px-3 bg-white text-muted">
                                    Password Settings
                                </span>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-12">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="current_password" 
                                            name="current_password" placeholder="Current password">
                                        <label for="current_password">Current Password</label>
                                        <div class="form-text mt-2">Required only if changing password</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="password" 
                                            name="password" placeholder="New password">
                                        <label for="password">New Password</label>
                                        <div class="form-text mt-2">Leave blank to keep current password</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="password_confirmation" 
                                            name="password_confirmation" placeholder="Confirm password">
                                        <label for="password_confirmation">Confirm New Password</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-5">
                                <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection