<?php 
$pageTitle = 'Change Password'; 
ob_start(); 
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2 text-primary"></i> Change Password</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= url('/change-password') ?>" method="POST">
                    <?= Csrf::field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" name="new_password" class="form-control" minlength="8" required>
                        <div class="form-text">Must be at least 8 characters long.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= url('/') ?>" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> Update Password</button>
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

