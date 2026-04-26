    <div class="card form-card shadow-lg border-0" style="max-width: 500px; margin: 40px auto;">
        <div class="card-body p-5">
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-4" style="width: 80px; height: 80px;">
                    <i class="bi bi-person-plus-fill fs-1"></i>
                </div>
                <h3 class="fw-bold mb-1">Create Account</h3>
                <p class="text-muted small">Join our premium dashboard system</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase tracking-wider">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                        <input id="name" type="text" class="form-control custom-input border-start-0 @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="John Doe" required autofocus autocomplete="name">
                    </div>
                    @error('name') <div class="text-danger xsmall mt-1">{{ $message }}</div> @enderror
                </div>

                <!-- Email Address -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase tracking-wider">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                        <input id="email" type="email" class="form-control custom-input border-start-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="name@company.com" required autocomplete="username">
                    </div>
                    @error('email') <div class="text-danger xsmall mt-1">{{ $message }}</div> @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase tracking-wider">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <input id="password" type="password" class="form-control custom-input border-start-0 border-end-0 @error('password') is-invalid @enderror" name="password" placeholder="••••••••" required autocomplete="new-password">
                        <button type="button" class="input-group-text bg-light border-start-0 toggle-password">
                            <i class="bi bi-eye text-muted"></i>
                        </button>
                    </div>
                    @error('password') <div class="text-danger xsmall mt-1">{{ $message }}</div> @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase tracking-wider">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-check text-muted"></i></span>
                        <input id="password_confirmation" type="password" class="form-control custom-input border-start-0 border-end-0 @error('password_confirmation') is-invalid @enderror" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
                        <button type="button" class="input-group-text bg-light border-start-0 toggle-password">
                            <i class="bi bi-eye text-muted"></i>
                        </button>
                    </div>
                    @error('password_confirmation') <div class="text-danger xsmall mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4 d-flex justify-content-end">
                    <a class="xsmall text-decoration-none" href="{{ route('login') }}">
                        Already registered? Log in
                    </a>
                </div>

                <button type="submit" class="btn btn-custom w-100 py-3 fw-bold shadow-sm">
                    Create Account <i class="bi bi-check2-circle ms-2"></i>
                </button>
            </form>
        </div>
    </div>
