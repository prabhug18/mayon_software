    <div class="card form-card shadow-lg border-0" style="max-width: 550px; margin: 40px auto;">
        <div class="card-body p-5">
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-4" style="width: 80px; height: 80px;">
                    <i class="bi bi-envelope-check-fill fs-1"></i>
                </div>
                <h3 class="fw-bold mb-1">Verify Email</h3>
                <p class="text-muted small">Last step to access your dashboard</p>
            </div>

            <div class="alert alert-info border-0 bg-light-subtle small mb-4">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success border-0 small mb-4">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </div>
            @endif

            <div class="d-flex flex-column gap-3">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-custom w-100 py-3 fw-bold shadow-sm">
                        Resend Verification Email <i class="bi bi-send-fill ms-2"></i>
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="text-center">
                    @csrf
                    <button type="submit" class="btn btn-link link-secondary small text-decoration-none">
                        <i class="bi bi-box-arrow-right me-1"></i> Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
