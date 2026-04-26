    <div class="card form-card shadow-lg border-0" style="max-width: 500px; margin: 40px auto;">
        <div class="card-body p-5">
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-4" style="width: 80px; height: 80px;">
                    <i class="bi bi-key-fill fs-1"></i>
                </div>
                <h3 class="fw-bold mb-1">Forgot Password?</h3>
                <p class="text-muted small">No problem. We'll send you a reset link.</p>
            </div>

            <div class="alert alert-info border-0 bg-light-subtle small mb-4">
                {{ __('Provide your email address and we will email you a password reset link that will allow you to choose a new one.') }}
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4 text-center text-success small" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div class="mb-5">
                    <label class="form-label small fw-bold text-uppercase tracking-wider">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                        <input id="email" type="email" class="form-control custom-input border-start-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="name@company.com" required autofocus>
                    </div>
                    @error('email') <div class="text-danger xsmall mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex flex-column gap-3">
                    <button type="submit" class="btn btn-custom w-100 py-3 fw-bold shadow-sm">
                        Email Password Reset Link <i class="bi bi-send-fill ms-2"></i>
                    </button>
                    <a href="{{ route('login') }}" class="btn btn-white border w-100 py-2 small text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>
