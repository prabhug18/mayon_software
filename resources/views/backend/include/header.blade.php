<div id="mainHeader"
    class="d-flex justify-content-between align-items-center px-4 py-2 bg-white text-dark shadow-sm w-100">
    <h4 class="fw-bold mb-0">{{ $heading }}</h4>
    <div class="d-flex align-items-center gap-3">
        <i class="bi bi-bell-fill fs-4" role="button" title="Notifications"></i>
        <!-- Profile Dropdown -->
        <!-- Profile Dropdown -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle"
                id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-4"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 p-2 animate__animated animate__fadeIn"
                aria-labelledby="profileDropdown">

                <!-- User Info -->
                <li class="px-3 py-2 border-bottom">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-circle fs-2 text-primary me-2"></i>
                        <div>
                            <h6 class="mb-0 fw-bold">Hello, {{ auth()->user()->name ?? 'User' }}</h6>
                            <small class="text-muted">{{ auth()->user()->email ?? '' }}</small>
                        </div>
                    </div>
                </li>
               
                <!-- Logout -->
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item d-flex align-items-center text-danger fw-bold py-2" style="border:0;background:transparent;width:100%;text-align:left;">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>