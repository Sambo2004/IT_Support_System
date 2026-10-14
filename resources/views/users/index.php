<?php 
$pageTitle = 'User Management'; 
ob_start(); 
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div>
        <h4 class="fw-bold mb-0 text-dark">User Management</h4>
        <p class="text-muted small mb-0">Manage system accounts, credentials, and access roles</p>
    </div>
    <a href="<?= url('/users/create') ?>" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i> Add User
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="ps-3 fw-bold text-dark"><?= e($u['full_name']) ?></td>
                        <td><code><?= e($u['username']) ?></code></td>
                        <td><?= e($u['email']) ?></td>
                        <td>
                            <?php 
                                $rColors = ['admin' => 'bg-danger', 'technician' => 'bg-primary', 'manager' => 'bg-warning text-dark', 'employee' => 'bg-info text-dark'];
                                $rc = $rColors[$u['role']] ?? 'bg-secondary';
                            ?>
                            <span class="badge <?= $rc ?>"><?= ucfirst($u['role']) ?></span>
                        </td>
                        <td><?= e($u['department_name'] ?? 'None') ?></td>
                        <td>
                            <?php if ($u['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted"><?= formatDateTime($u['last_login']) ?></td>
                        <td class="text-end pe-3">
                            <a href="<?= url('/users/' . $u['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="<?= url('/users/' . $u['id'] . '/toggle') ?>" method="POST" class="d-inline" onsubmit="return confirm('Toggle active status for this user?');">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn btn-sm <?= $u['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="Toggle Status">
                                    <i class="bi <?= $u['is_active'] ? 'bi-person-x' : 'bi-person-check' ?>"></i>
                                </button>
                            </form>
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

