<?php 
$pageTitle = 'IT Assets'; 
ob_start(); 
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div>
        <h4 class="fw-bold mb-0 text-dark">IT Asset Management</h4>
        <p class="text-muted small mb-0">Hardware inventory, devices, and employee equipment assignments</p>
    </div>
    <?php if (isRole('admin')): ?>
    <a href="<?= url('/assets/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add Asset
    </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="<?= url('/assets') ?>" method="GET" class="row g-2 align-items-center filter-form-responsive">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search asset tag, device, serial..." value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="available" <?= ($filters['status'] === 'available') ? 'selected' : '' ?>>Available</option>
                    <option value="assigned" <?= ($filters['status'] === 'assigned') ? 'selected' : '' ?>>Assigned</option>
                    <option value="under_maintenance" <?= ($filters['status'] === 'under_maintenance') ? 'selected' : '' ?>>Under Maintenance</option>
                    <option value="retired" <?= ($filters['status'] === 'retired') ? 'selected' : '' ?>>Retired</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Asset Categories</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (($filters['category_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-dark w-100">Filter</button>
                <a href="<?= url('/assets') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Asset Tag</th>
                        <th>Device Name</th>
                        <th>Category</th>
                        <th>Assigned To</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Warranty Expiry</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assets)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No IT assets found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($assets as $ast): ?>
                        <tr>
                            <td class="ps-3 fw-bold text-primary"><code><?= e($ast['asset_tag']) ?></code></td>
                            <td>
                                <strong><?= e($ast['device_name']) ?></strong>
                                <?php if (!empty($ast['brand'])): ?>
                                    <div class="small text-muted"><?= e($ast['brand']) ?> <?= e($ast['model'] ?? '') ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= e($ast['category_name']) ?></td>
                            <td><?= e($ast['assigned_to_name'] ?? 'Not assigned') ?></td>
                            <td><?= e($ast['department_name'] ?? '-') ?></td>
                            <td>
                                <?php 
                                    $aColors = ['available' => 'bg-success', 'assigned' => 'bg-primary', 'under_maintenance' => 'bg-warning text-dark', 'retired' => 'bg-secondary'];
                                    $ac = $aColors[$ast['status']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?= $ac ?>"><?= ucwords(str_replace('_', ' ', $ast['status'])) ?></span>
                            </td>
                            <td class="small text-muted"><?= formatDate($ast['warranty_expiry']) ?></td>
                            <td class="text-end pe-3">
                                <?php if (isRole('admin')): ?>
                                <a href="<?= url('/assets/' . $ast['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="<?= url('/assets/' . $ast['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this asset? This cannot be undone if not referenced by tickets.');">
                                    <?= Csrf::field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Asset">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require BASE_PATH . '/resources/views/layouts/app.php'; 
?>

