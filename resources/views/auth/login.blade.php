@extends('layouts.guest')

@section('content')
    <div class="card form-card shadow-lg border-0" style="max-width: 450px; margin: 40px auto;">
        <div class="card-body p-5">
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-4" style="width: 80px; height: 80px;">
                    <i class="bi bi-shield-lock-fill fs-1"></i>
                </div>
                <h3 class="fw-bold mb-1">Welcome Back</h3>
                <p class="text-muted small">Login to your premium dashboard</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4 text-center text-success small" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase tracking-wider">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                        <input id="email" type="email" class="form-control custom-input border-start-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="name@company.com" required autofocus autocomplete="username">
                    </div>
                    @error('email') <div class="text-danger xsmall mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold text-uppercase tracking-wider mb-0">Password</label>
                        @if (Route::has('password.request'))
                            <a class="xsmall text-decoration-none" href="{{ route('password.request') }}">Forgot password?</a>
                        @endif
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <input id="password" type="password" class="form-control custom-input border-start-0 border-end-0 @error('password') is-invalid @enderror" name="password" placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="input-group-text bg-light border-start-0 toggle-password" id="togglePassword">
                            <i class="bi bi-eye text-muted"></i>
                        </button>
                    </div>
                    @error('password') <div class="text-danger xsmall mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                        <label class="form-check-label small text-muted" for="remember_me">Remember me on this device</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-custom w-100 py-3 fw-bold shadow-sm">
                    Log in <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </form>
        </div>
    </div>
@endsection
