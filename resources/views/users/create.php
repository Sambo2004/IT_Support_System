<?php 
$pageTitle = 'Add New User'; 
ob_start(); 
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus-fill text-primary me-2"></i>Create User Account</h5>
                <a href="<?= url('/users') ?>" class="btn btn-sm btn-outline-secondary">Back to Users</a>
            </div>
            <div class="card-body p-4">
                <form action="<?= url('/users') ?>" method="POST">
                    <?= Csrf::field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" value="<?= e(old('full_name')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" value="<?= e(old('username')) ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= e(old('email')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?= e(old('phone')) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="employee" <?= (old('role') === 'employee') ? 'selected' : '' ?>>Employee</option>
                                <option value="technician" <?= (old('role') === 'technician') ? 'selected' : '' ?>>IT Support Technician</option>
                                <option value="manager" <?= (old('role') === 'manager') ? 'selected' : '' ?>>Department Manager</option>
                                <option value="admin" <?= (old('role') === 'admin') ? 'selected' : '' ?>>System Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">No Department Assigned</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" <?= (old('department_id') == $dept['id']) ? 'selected' : '' ?>>
                                        <?= e($dept['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Initial Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" minlength="8" required>
                        <div class="form-text">Minimum 8 characters.</div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                        <a href="<?= url('/users') ?>" class="btn btn-light border order-2 order-sm-1">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 order-1 order-sm-2">Save User</button>
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

