<?php 
$pageTitle = 'Dashboard'; 
ob_start(); 
?>

<!-- Stat Cards Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Tickets</div>
                        <h3 class="fw-bold mb-0 text-dark"><?= $totalTickets ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                        <i class="bi bi-ticket-detailed fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Open / Assigned</div>
                        <h3 class="fw-bold mb-0 text-dark"><?= $openTickets ?></h3>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info rounded p-3">
                        <i class="bi bi-exclamation-circle fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">In Progress</div>
                        <h3 class="fw-bold mb-0 text-dark"><?= $inProgressTickets ?></h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                        <i class="bi bi-hourglass-split fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Resolved / Closed</div>
                        <h3 class="fw-bold mb-0 text-dark"><?= $resolvedTickets ?></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded p-3">
                        <i class="bi bi-check-circle fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Critical & High Priority Alerts -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-shield-exclamation text-danger me-2"></i>Urgent Attention</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center p-3 mb-3 bg-danger bg-opacity-10 rounded">
                    <div>
                        <span class="badge bg-danger">Critical</span>
                        <div class="small text-muted mt-1">Requires immediate response</div>
                    </div>
                    <h2 class="fw-bold text-danger mb-0"><?= $criticalTickets ?></h2>
                </div>

                <div class="d-flex justify-content-between align-items-center p-3 bg-warning bg-opacity-10 rounded">
                    <div>
                        <span class="badge bg-warning text-dark">High Priority</span>
                        <div class="small text-muted mt-1">Expedite resolution</div>
                    </div>
                    <h2 class="fw-bold text-dark mb-0"><?= $highTickets ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Breakdown -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-pie-chart me-2 text-primary"></i>Tickets by Status</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php 
                    $statuses = ['open', 'assigned', 'in_progress', 'pending', 'resolved', 'closed'];
                    foreach ($statuses as $st): 
                        $cnt = $statusCounts[$st] ?? 0;
                    ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span><?= statusBadge($st) ?></span>
                        <span class="fw-bold text-dark"><?= $cnt ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- IT Asset Overview -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-pc-display me-2 text-primary"></i>Hardware Inventory</h6>
                <?php if (isRole('admin', 'technician')): ?>
                    <a href="<?= url('/assets') ?>" class="btn btn-sm btn-link p-0 text-decoration-none small">Manage</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span><i class="bi bi-check2 text-success me-2"></i>Available</span>
                        <span class="badge bg-success"><?= $assetCounts['available'] ?? 0 ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span><i class="bi bi-person-check text-primary me-2"></i>Assigned</span>
                        <span class="badge bg-primary"><?= $assetCounts['assigned'] ?? 0 ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span><i class="bi bi-tools text-warning me-2"></i>Under Maintenance</span>
                        <span class="badge bg-warning text-dark"><?= $assetCounts['under_maintenance'] ?? 0 ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span><i class="bi bi-archive text-secondary me-2"></i>Retired</span>
                        <span class="badge bg-secondary"><?= $assetCounts['retired'] ?? 0 ?></span>
                    </li>
                </ul>

                <?php if (!empty($expiringWarranties) && isRole('admin', 'technician')): ?>
                    <div class="alert alert-warning p-2 mb-0 small">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Expiring Warranties (&le; 90d):</div>
                        <?php foreach (array_slice($expiringWarranties, 0, 3) as $ew): ?>
                            <div class="d-flex justify-content-between">
                                <span><?= e($ew['asset_tag']) ?> (<?= e($ew['device_name']) ?>)</span>
                                <span class="fw-semibold text-danger"><?= $ew['days_remaining'] ?>d left</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Tickets Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Tickets</h5>
        <a href="<?= url('/tickets') ?>" class="btn btn-sm btn-outline-primary">View All Tickets</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Ticket #</th>
                        <th>Title</th>
                        <th>Requester</th>
                        <th>Assigned Tech</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentTickets)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No tickets found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentTickets as $t): ?>
                        <tr>
                            <td class="ps-3 fw-bold text-primary"><?= e($t['ticket_number']) ?></td>
                            <td>
                                <a href="<?= url('/tickets/' . $t['id']) ?>" class="text-decoration-none text-dark fw-semibold">
                                    <?= e(mb_strimwidth($t['title'], 0, 50, '...')) ?>
                                </a>
                            </td>
                            <td><?= e($t['requester_name']) ?></td>
                            <td><?= e($t['assigned_name'] ?? 'Unassigned') ?></td>
                            <td><?= priorityBadge($t['priority']) ?></td>
                            <td><?= statusBadge($t['status']) ?></td>
                            <td class="small text-muted"><?= formatDate($t['created_at']) ?></td>
                            <td class="text-end pe-3">
                                <a href="<?= url('/tickets/' . $t['id']) ?>" class="btn btn-sm btn-light border">
                                    <i class="bi bi-eye"></i> View
                                </a>
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

