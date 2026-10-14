<?php 
$pageTitle = 'Departments'; 
ob_start(); 
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div>
        <h4 class="fw-bold mb-0 text-dark">Company Departments</h4>
        <p class="text-muted small mb-0">Organizational units for categorizing personnel and support tickets</p>
    </div>
    <a href="<?= url('/departments/create') ?>" class="btn btn-primary">
        <i class="bi bi-building-add me-1"></i> Add Department
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Department Name</th>
                        <th>Description</th>
                        <th>Assigned Staff</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $d): ?>
                    <tr>
                        <td class="ps-3 fw-bold text-dark"><?= e($d['name']) ?></td>
                        <td><?= e($d['description'] ?? '-') ?></td>
                        <td><span class="badge bg-light text-dark border"><?= $d['user_count'] ?? 0 ?> members</span></td>
                        <td>
                            <?php if ($d['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-3">
                            <a href="<?= url('/departments/' . $d['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require BASE_PATH . '/resources/views/layouts/app.php'; 
?>

