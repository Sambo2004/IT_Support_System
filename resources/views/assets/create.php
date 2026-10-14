<?php 
$pageTitle = 'Add IT Asset'; 
ob_start(); 
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pc-display text-primary me-2"></i>Register New IT Asset</h5>
                <a href="<?= url('/assets') ?>" class="btn btn-sm btn-outline-secondary">Back to Assets</a>
            </div>
            <div class="card-body p-4">
                <form action="<?= url('/assets') ?>" method="POST">
                    <?= Csrf::field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Asset Tag <span class="text-danger">*</span></label>
                            <input type="text" name="asset_tag" class="form-control" placeholder="e.g. AST-2026-005" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Device Name <span class="text-danger">*</span></label>
                            <input type="text" name="device_name" class="form-control" placeholder="e.g. MacBook Pro M3" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select category...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Serial Number</label>
                            <input type="text" name="serial_number" class="form-control" placeholder="Manufacturer S/N">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Brand / Manufacturer</label>
                            <input type="text" name="brand" class="form-control" placeholder="e.g. Apple, Dell, Lenovo">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Model</label>
                            <input type="text" name="model" class="form-control" placeholder="e.g. Latitude 5420">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Purchase Date</label>
                            <input type="date" name="purchase_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Warranty Expiration</label>
                            <input type="date" name="warranty_expiry" class="form-control">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assigned Employee</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Unassigned (In stock)</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= e($u['full_name']) ?> (<?= e($u['role']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">Select department...</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= e($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Physical Location</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Building B, Room 204">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Asset Status</label>
                            <select name="status" class="form-select">
                                <option value="available">Available</option>
                                <option value="assigned">Assigned</option>
                                <option value="under_maintenance">Under Maintenance</option>
                                <option value="retired">Retired</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Notes & Specifications</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="RAM, storage, MAC address, peripherals included..."></textarea>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                        <a href="<?= url('/assets') ?>" class="btn btn-light border order-2 order-sm-1">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 order-1 order-sm-2">Register Asset</button>
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

