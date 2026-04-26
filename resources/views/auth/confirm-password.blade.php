    <div class="card form-card shadow-lg border-0" style="max-width: 500px; margin: 40px auto;">
        <div class="card-body p-5">
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-4" style="width: 80px; height: 80px;">
                    <i class="bi bi-shield-check fs-1"></i>
                </div>
                <h3 class="fw-bold mb-1">Confirm Access</h3>
                <p class="text-muted small">Please confirm your password to continue</p>
            </div>

            <div class="alert alert-warning border-0 bg-warning-subtle small mb-4">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <!-- Password -->
                <div class="mb-5">
                    <label class="form-label small fw-bold text-uppercase tracking-wider">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <input id="password" type="password" class="form-control custom-input border-start-0 @error('password') is-invalid @enderror" name="password" placeholder="••••••••" required autocomplete="current-password">
                    </div>
                    @error('password') <div class="text-danger xsmall mt-1">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-custom w-100 py-3 fw-bold shadow-sm">
                    Confirm Password <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </form>
        </div>
    </div>
