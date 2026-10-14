<?php 
$pageTitle = 'System Health & Diagnostics'; 
ob_start(); 
$allHealthy = !in_array(false, array_column($checks, 'status'), true);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1"><i class="bi bi-heart-pulse-fill text-danger me-2"></i>System Health & Infrastructure Diagnostics</h4>
        <p class="text-muted small mb-0">Live status monitoring for database, storage, sessions, and environment runtime.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/system/health?format=json') ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-code-slash me-1"></i> JSON Endpoint
        </a>
        <a href="<?= url('/system/health') ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm p-3 <?= $allHealthy ? 'bg-success text-white' : 'bg-warning text-dark' ?>">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi <?= $allHealthy ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> fs-1"></i>
                    <div>
                        <h5 class="fw-bold mb-1"><?= $allHealthy ? 'System Operational & Fully Healthy' : 'System Degraded / Action Required' ?></h5>
                        <div class="small opacity-75">All core services, database connections, and writable directories inspected.</div>
                    </div>
                </div>
                <div class="text-end d-none d-md-block">
                    <span class="badge bg-white text-dark px-3 py-2 fw-bold">Latency: <?= $dbLatency ?>ms</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <?php foreach ($checks as $key => $check): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold text-dark mb-0"><?= e($check['name']) ?></h6>
                            <?php if ($check['status']): ?>
                                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-2 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i> PASS
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 px-2 py-1">
                                    <i class="bi bi-x-circle-fill me-1"></i> FAIL
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="fs-5 fw-semibold text-primary mb-2">
                            <?= e($check['value']) ?>
                        </div>
                    </div>
                    <div class="pt-2 border-top text-muted small text-truncate" title="<?= e($check['details']) ?>">
                        <?= e($check['details']) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php 
$content = ob_get_clean(); 
require BASE_PATH . '/resources/views/layouts/app.php'; 
?>

