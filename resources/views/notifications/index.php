<?php 
$pageTitle = 'Notifications'; 
ob_start(); 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark">Activity Notifications</h4>
        <p class="text-muted small mb-0">System updates, assignment alerts, and ticket replies</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($notifications)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-bell-slash fs-1 text-secondary"></i>
                <h6 class="mt-3">No notifications right now</h6>
                <p class="small text-muted mb-0">You're all caught up with your support activities.</p>
            </div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($notifications as $n): ?>
                <div class="list-group-item p-3 d-flex justify-content-between align-items-center <?= $n['is_read'] ? '' : 'bg-light border-start border-primary border-4' ?>">
                    <div>
                        <div class="d-flex align-items-center mb-1">
                            <strong class="text-dark me-2"><?= e($n['title']) ?></strong>
                            <?php if (!$n['is_read']): ?>
                                <span class="badge bg-primary rounded-pill small">New</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted small"><?= e($n['message']) ?></div>
                        <small class="text-secondary"><?= formatDateTime($n['created_at']) ?></small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <?php if (!empty($n['link'])): ?>
                            <a href="<?= url($n['link']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                        <?php endif; ?>
                        <?php if (!$n['is_read']): ?>
                            <form action="<?= url('/notifications/' . $n['id'] . '/read') ?>" method="POST" class="d-inline">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn btn-sm btn-light border" title="Mark as Read">
                                    <i class="bi bi-check2"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require BASE_PATH . '/resources/views/layouts/app.php'; 
?>

