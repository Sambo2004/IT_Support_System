<?php 
$pageTitle = 'My Profile'; 
ob_start(); 
?>

<div class="row g-4">
    <!-- Left Column: User Profile Overview Card -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm text-center p-4">
            <div class="mx-auto mb-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow"
                 style="width: 80px; height: 80px; font-size: 2rem; font-weight: 700;">
                <?= e(strtoupper(substr($user['full_name'] ?? 'U', 0, 1))) ?>
            </div>
            <h5 class="fw-bold text-dark mb-1"><?= e($user['full_name']) ?></h5>
            <div class="text-muted small mb-2">@<?= e($user['username']) ?></div>
            
            <div class="mb-3">
                <span class="badge bg-primary px-3 py-2 text-uppercase fs-7"><?= e($user['role']) ?></span>
                <?php if ($user['is_active']): ?>
                    <span class="badge bg-success px-2 py-2"><i class="bi bi-check-circle me-1"></i>Active</span>
                <?php else: ?>
                    <span class="badge bg-secondary px-2 py-2">Inactive</span>
                <?php endif; ?>
            </div>

            <hr class="my-3">

            <div class="text-start small">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted"><i class="bi bi-building me-2"></i>Department:</span>
                    <strong class="text-dark"><?= e($user['department_name'] ?? 'Unassigned') ?></strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted"><i class="bi bi-calendar-event me-2"></i>Member Since:</span>
                    <span><?= formatDate($user['created_at']) ?></span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted"><i class="bi bi-clock-history me-2"></i>Last Login:</span>
                    <span><?= formatDateTime($user['last_login'] ?? null) ?></span>
                </div>
            </div>
        </div>

        <!-- Security Quick Link Card -->
        <div class="card border-0 shadow-sm mt-4 p-3 bg-light">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="fw-bold mb-1 text-dark"><i class="bi bi-shield-lock text-primary me-2"></i>Account Security</h6>
                    <p class="text-muted small mb-0">Update your account password regularly.</p>
                </div>
                <a href="<?= url('/change-password') ?>" class="btn btn-sm btn-outline-primary fw-semibold">
                    Change Password
                </a>
            </div>
        </div>
    </div>

    <!-- Right Column: Profile Edit Form -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 fw-bold"><i class="bi bi-person-gear text-primary me-2"></i>Edit Profile Information</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= url('/profile') ?>" method="POST">
                    <?= Csrf::field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Username</label>
                            <input type="text" class="form-control bg-light" value="<?= e($user['username']) ?>" readonly disabled>
                            <div class="form-text small">System username cannot be modified.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Role & Permissions</label>
                            <input type="text" class="form-control bg-light" value="<?= e(ucfirst($user['role'])) ?>" readonly disabled>
                            <div class="form-text small">Assigned by System Administrator.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="full_name" class="form-label fw-semibold small">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?= e($user['full_name']) ?>" required>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= e($user['email']) ?>" required>
                            <div class="form-text small">Used for ticket notifications & updates.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-semibold small">Contact Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?= e($user['phone'] ?? '') ?>" placeholder="+855 12 345 678">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= url('/dashboard') ?>" class="btn btn-light border px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require BASE_PATH . '/resources/views/layouts/app.php'; 
?>

