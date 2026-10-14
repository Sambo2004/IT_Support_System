<?php 
$pageTitle = 'Ticket Categories'; 
ob_start(); 
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div>
        <h4 class="fw-bold mb-0 text-dark">Ticket Categories</h4>
        <p class="text-muted small mb-0">Help desk problem taxonomy and classification</p>
    </div>
    <a href="<?= url('/categories/create') ?>" class="btn btn-primary">
        <i class="bi bi-tag-fill me-1"></i> Add Category
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Category Name</th>
                        <th>Description</th>
                        <th>Total Tickets</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                    <tr>
                        <td class="ps-3 fw-bold text-dark"><?= e($c['name']) ?></td>
                        <td><?= e($c['description'] ?? '-') ?></td>
                        <td><span class="badge bg-light text-dark border"><?= $c['ticket_count'] ?? 0 ?> tickets</span></td>
                        <td>
                            <?php if ($c['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-3">
                            <a href="<?= url('/categories/' . $c['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary">
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

