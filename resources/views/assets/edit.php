<?php 
$pageTitle = 'Edit IT Asset'; 
ob_start(); 
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Edit Asset: <?= e($asset['asset_tag']) ?></h5>
                <a href="<?= url('/assets') ?>" class="btn btn-sm btn-outline-secondary">Back to Assets</a>
            </div>
            <div class="card-body p-4">
                <form action="<?= url('/assets/' . $asset['id']) ?>" method="POST">
                    <?= Csrf::field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Asset Tag</label>
                            <input type="text" name="asset_tag" class="form-control" value="<?= e($asset['asset_tag']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Device Name</label>
                            <input type="text" name="device_name" class="form-control" value="<?= e($asset['device_name']) ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($asset['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Serial Number</label>
                            <input type="text" name="serial_number" class="form-control" value="<?= e($asset['serial_number'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Brand</label>
                            <input type="text" name="brand" class="form-control" value="<?= e($asset['brand'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Model</label>
                            <input type="text" name="model" class="form-control" value="<?= e($asset['model'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Purchase Date</label>
                            <input type="date" name="purchase_date" class="form-control" value="<?= e($asset['purchase_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Warranty Expiration</label>
                            <input type="date" name="warranty_expiry" class="form-control" value="<?= e($asset['warranty_expiry'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assigned Employee</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Unassigned</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= ($asset['assigned_to'] == $u['id']) ? 'selected' : '' ?>>
                                        <?= e($u['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">Select department...</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" <?= ($asset['department_id'] == $dept['id']) ? 'selected' : '' ?>>
                                        <?= e($dept['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Physical Location</label>
                            <input type="text" name="location" class="form-control" value="<?= e($asset['location'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Asset Status</label>
                            <select name="status" class="form-select">
                                <?php foreach (['available', 'assigned', 'under_maintenance', 'retired'] as $st): ?>
                                    <option value="<?= $st ?>" <?= ($asset['status'] === $st) ? 'selected' : '' ?>>
                                        <?= ucwords(str_replace('_', ' ', $st)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"><?= e($asset['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                        <a href="<?= url('/assets') ?>" class="btn btn-light border order-2 order-sm-1">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 order-1 order-sm-2">Update Asset</button>
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

