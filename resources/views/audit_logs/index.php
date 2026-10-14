<?php 
$pageTitle = 'Security & Audit Logs'; 
ob_start(); 
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div>
        <h4 class="fw-bold mb-0 text-dark">System Audit Trail</h4>
        <p class="text-muted small mb-0">Immutable records of authentication events, CRUD actions, and permission operations</p>
    </div>
    <span class="badge bg-dark px-3 py-2">Total Logs: <?= $total ?></span>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Entity Type</th>
                        <th>Entity ID</th>
                        <th>IP Address</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No audit records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="ps-3 small text-muted"><?= formatDateTime($log['created_at']) ?></td>
                            <td>
                                <?php if (!empty($log['full_name'])): ?>
                                    <strong><?= e($log['full_name']) ?></strong>
                                    <div class="small text-muted">@<?= e($log['username']) ?></div>
                                <?php else: ?>
                                    <span class="text-muted">System / Anonymous</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $aBadge = 'bg-primary';
                                    if (str_contains($log['action'], 'Failed') || str_contains($log['action'], 'Delete')) $aBadge = 'bg-danger';
                                    elseif (str_contains($log['action'], 'Create')) $aBadge = 'bg-success';
                                    elseif (str_contains($log['action'], 'Update') || str_contains($log['action'], 'Change')) $aBadge = 'bg-info text-dark';
                                ?>
                                <span class="badge <?= $aBadge ?>"><?= e($log['action']) ?></span>
                            </td>
                            <td><code><?= e($log['entity_type']) ?></code></td>
                            <td><?= e($log['entity_id'] ?? '-') ?></td>
                            <td><small class="text-muted"><?= e($log['ip_address'] ?? '127.0.0.1') ?></small></td>
                            <td class="small text-dark"><?= e($log['details'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
require BASE_PATH . '/resources/views/partials/pagination.php';
$content = ob_get_clean(); 
require BASE_PATH . '/resources/views/layouts/app.php'; 
?>

