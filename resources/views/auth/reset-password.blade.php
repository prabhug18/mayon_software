    <div class="card form-card shadow-lg border-0" style="max-width: 500px; margin: 40px auto;">
        <div class="card-body p-5">
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-4" style="width: 80px; height: 80px;">
                    <i class="bi bi-shield-lock-fill fs-1"></i>
                </div>
                <h3 class="fw-bold mb-1">Reset Password</h3>
                <p class="text-muted small">Secure your account with a new password</p>
            </div>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase tracking-wider">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                        <input id="email" type="email" class="form-control custom-input border-start-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
                    </div>
                    @error('email') <div class="text-danger xsmall mt-1">{{ $message }}</div> @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase tracking-wider">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <input id="password" type="password" class="form-control custom-input border-start-0 @error('password') is-invalid @enderror" name="password" placeholder="••••••••" required autocomplete="new-password">
                    </div>
                    @error('password') <div class="text-danger xsmall mt-1">{{ $message }}</div> @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-5">
                    <label class="form-label small fw-bold text-uppercase tracking-wider">Confirm New Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-check text-muted"></i></span>
                        <input id="password_confirmation" type="password" class="form-control custom-input border-start-0 @error('password_confirmation') is-invalid @enderror" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
                    </div>
                    @error('password_confirmation') <div class="text-danger xsmall mt-1">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-custom w-100 py-3 fw-bold shadow-sm">
                    Reset Password <i class="bi bi-check2-circle ms-2"></i>
                </button>
            </form>
        </div>
    </div>
