<?php 
$pageTitle = 'Edit User'; 
ob_start(); 
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Edit User: <?= e($user['username']) ?></h5>
                <a href="<?= url('/users') ?>" class="btn btn-sm btn-outline-secondary">Back to Users</a>
            </div>
            <div class="card-body p-4">
                <form action="<?= url('/users/' . $user['id']) ?>" method="POST">
                    <?= Csrf::field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role</label>
                            <select name="role" class="form-select" required>
                                <?php foreach (['employee' => 'Employee', 'technician' => 'IT Support Technician', 'manager' => 'Department Manager', 'admin' => 'System Administrator'] as $rk => $rlabel): ?>
                                    <option value="<?= $rk ?>" <?= ($user['role'] === $rk) ? 'selected' : '' ?>>
                                        <?= $rlabel ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">No Department Assigned</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= ($user['department_id'] == $dept['id']) ? 'selected' : '' ?>>
                                    <?= e($dept['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Reset Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                        <a href="<?= url('/users') ?>" class="btn btn-light border order-2 order-sm-1">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 order-1 order-sm-2">Update User</button>
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

