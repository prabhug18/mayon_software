@extends('layouts.guest')

@section('content')
    <div class="login-card">
        <div class="login-header">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="" class="logo-circle">
            <!-- <h2>ESR</h2> -->
            <p class="text-muted">Login to your dashboard</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-3 text-center text-success" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Enter your email" required autofocus autocomplete="username">
                </div>
                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Enter password" required autocomplete="current-password" minlength="8">
                </div>
                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-login w-100">{{ __('Log in') }}</button>
        </form>
    </div>
@endsection
