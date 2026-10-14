<?php 
$pageTitle = 'Sign In'; 
ob_start(); 
?>

<div class="auth-card-wrap">
    <div class="row g-0">
        <!-- Left Hero Showcase (visible on desktop) -->
        <div class="col-lg-6 d-none d-lg-flex auth-hero-side">
            <div>
                <div class="brand-badge mb-3">
                    <i class="bi bi-shield-check"></i> Enterprise IT Portal
                </div>
                <h2 class="fw-bold display-6 mb-3 text-white">Centralized IT Service Management</h2>
                <p class="text-white-50 mb-4" style="line-height: 1.6;">
                    Streamline enterprise incident resolution, track IT assets and warranties, and enforce SLA response benchmarks in a single secured workspace.
                </p>

                <div class="auth-hero-feature">
                    <i class="bi bi-speedometer2"></i>
                    <div>
                        <strong class="d-block text-white">SLA-Driven Lifecycle</strong>
                        <span>Real-time resolution countdowns & priority routing</span>
                    </div>
                </div>

                <div class="auth-hero-feature">
                    <i class="bi bi-pc-display"></i>
                    <div>
                        <strong class="d-block text-white">Asset & Warranty Control</strong>
                        <span>Lifecycle management with automatic 90-day expiry notices</span>
                    </div>
                </div>

                <div class="auth-hero-feature">
                    <i class="bi bi-lock-fill"></i>
                    <div>
                        <strong class="d-block text-white">Hardened Role-Based Security</strong>
                        <span>Isolated attachments, audit trail, and rate limiting</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-top border-secondary border-opacity-25 d-flex align-items-center justify-content-between text-white-50 small">
                <span class="d-flex align-items-center gap-2">
                    <span class="badge bg-success rounded-circle p-1" style="width: 8px; height: 8px;"></span>
                    All Services Operational
                </span>
                <span>Fast & Reliable Support</span>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="col-lg-6 auth-form-side">
            <div class="mb-4">
                <div class="d-flex align-items-center gap-2 mb-2 d-lg-none">
                    <i class="bi bi-headset fs-2 text-primary"></i>
                    <h5 class="fw-bold text-dark mb-0"><?= e(APP_NAME) ?></h5>
                </div>
                <h3 class="fw-bold text-dark mb-1">Sign In</h3>
                <p class="text-muted small mb-0">Enter your credentials to access your support portal.</p>
            </div>

            <?php require BASE_PATH . '/resources/views/partials/alerts.php'; ?>

            <form action="<?= url('/login') ?>" method="POST" id="loginForm">
                <?= Csrf::field() ?>

                <div class="mb-3 position-relative form-floating-custom">
                    <label for="username" class="form-label small fw-semibold text-secondary">Username or Email</label>
                    <div class="position-relative">
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?= e(old('username')) ?>" placeholder="Enter your username" required autofocus autocomplete="username">
                        <i class="bi bi-person input-icon"></i>
                    </div>
                </div>

                <div class="mb-3 position-relative form-floating-custom">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="password" class="form-label small fw-semibold text-secondary mb-0">Password</label>
                    </div>
                    <div class="position-relative">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter your password" required autocomplete="current-password">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <button type="button" class="password-toggle-btn" id="togglePasswordBtn" aria-label="Toggle password visibility">
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                    <div id="capsLockWarning" class="caps-lock-warning">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Caps Lock is ON
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe" name="remember_me" value="1">
                        <label class="form-check-label small text-secondary" for="rememberMe">
                            Remember username
                        </label>
                    </div>
                    <span class="small text-muted"><i class="bi bi-shield-check text-success me-1"></i>256-bit Secure</span>
                </div>

                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-brand-primary d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Sign In to Workspace</span>
                    </button>
                </div>

                <!-- Quick Switcher Demo Credentials -->
                <div class="border rounded p-3 bg-light bg-opacity-50">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-bold text-dark"><i class="bi bi-key-fill text-primary me-1"></i> Quick Demo Access:</span>
                        <span class="text-muted" style="font-size: 0.75rem;">Password: <code>password</code></span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="demo-pill" onclick="fillCredentials('admin')">
                            <i class="bi bi-person-fill-gear text-danger me-1"></i> Admin
                        </button>
                        <button type="button" class="demo-pill" onclick="fillCredentials('tech1')">
                            <i class="bi bi-wrench-adjustable text-primary me-1"></i> Tech 1
                        </button>
                        <button type="button" class="demo-pill" onclick="fillCredentials('employee1')">
                            <i class="bi bi-person text-info me-1"></i> Employee
                        </button>
                        <button type="button" class="demo-pill" onclick="fillCredentials('manager1')">
                            <i class="bi bi-briefcase text-warning me-1"></i> Manager
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Password visibility toggle
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePasswordIcon');
    const usernameInput = document.getElementById('username');
    const capsWarning = document.getElementById('capsLockWarning');

    if (toggleBtn && passwordInput && toggleIcon) {
        toggleBtn.addEventListener('click', function() {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            toggleIcon.className = isPassword ? 'bi bi-eye-slash text-primary' : 'bi bi-eye';
        });
    }

    // Caps Lock indicator
    if (passwordInput && capsWarning) {
        ['keydown', 'keyup'].forEach(evt => {
            passwordInput.addEventListener(evt, function(e) {
                if (e.getModifierState && e.getModifierState('CapsLock')) {
                    capsWarning.style.display = 'block';
                } else {
                    capsWarning.style.display = 'none';
                }
            });
        });
    }

    // Quick fill helper
    function fillCredentials(user) {
        if (usernameInput && passwordInput) {
            usernameInput.value = user;
            passwordInput.value = 'password';
            passwordInput.focus();
        }
    }

    // Remember username local storage persistence
    const rememberMeCheck = document.getElementById('rememberMe');
    const savedUser = localStorage.getItem('itsupport_saved_username');
    if (savedUser && usernameInput) {
        if (!usernameInput.value) {
            usernameInput.value = savedUser;
            if (rememberMeCheck) rememberMeCheck.checked = true;
        }
    }

    document.getElementById('loginForm')?.addEventListener('submit', function() {
        if (rememberMeCheck?.checked && usernameInput?.value) {
            localStorage.setItem('itsupport_saved_username', usernameInput.value);
        } else {
            localStorage.removeItem('itsupport_saved_username');
        }
    });
</script>

<?php 
$content = ob_get_clean(); 
require BASE_PATH . '/resources/views/layouts/auth.php'; 
?>
