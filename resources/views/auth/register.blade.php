@extends('layouts.app')

@section('content')
<div class="min-vh-100 d-flex align-items-center py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white py-3">
                        <h4 class="mb-0">Register</h4>
                    </div>
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="name" name="name" value="{{ old('name') }}" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email" value="{{ old('email') }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control form-control-lg" id="password_confirmation" name="password_confirmation" required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Register</button>
                                <div class="text-center mt-3">
                                    <a href="{{ route('login') }}" class="text-decoration-none">Already have an account? Login</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection