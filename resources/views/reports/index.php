<?php 
$pageTitle = 'Reports & Service Desk Analytics'; 
ob_start(); 
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div>
        <h4 class="fw-bold mb-0 text-dark">Reports & Analytics</h4>
        <p class="text-muted small mb-0">Operational performance, ticket distributions, and resolution statistics</p>
    </div>
    <a href="<?= url('/reports/export?' . http_build_query(['from' => $from, 'to' => $to])) ?>" class="btn btn-outline-success">
        <i class="bi bi-file-earmark-excel me-1"></i> Export to CSV
    </a>
</div>

<!-- Date Filter Form -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="<?= url('/reports') ?>" method="GET" class="row g-2 align-items-center filter-form-responsive">
            <div class="col-md-4 d-flex align-items-center">
                <label class="form-label small fw-semibold me-2 mb-0">From:</label>
                <input type="date" name="from" class="form-control form-control-sm" value="<?= e($from) ?>" required>
            </div>
            <div class="col-md-4 d-flex align-items-center">
                <label class="form-label small fw-semibold me-2 mb-0">To:</label>
                <input type="date" name="to" class="form-control form-control-sm" value="<?= e($to) ?>" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-funnel me-1"></i> Update Report Range
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Top Statistics Highlights -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="text-muted small fw-semibold">PERIOD TOTAL TICKETS</div>
            <h3 class="fw-bold mb-0 text-primary mt-1"><?= $totalTickets ?></h3>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="text-muted small fw-semibold">RESOLVED / CLOSED</div>
            <h3 class="fw-bold mb-0 text-success mt-1"><?= $resolvedCount ?></h3>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="text-muted small fw-semibold">RESOLUTION RATE</div>
            <h3 class="fw-bold mb-0 text-dark mt-1">
                <?= $totalTickets > 0 ? round(($resolvedCount / $totalTickets) * 100, 1) : 0 ?>%
            </h3>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3">
            <div class="text-muted small fw-semibold">CRITICAL INCIDENTS</div>
            <h3 class="fw-bold mb-0 text-danger mt-1"><?= $priorityCounts['critical'] ?? 0 ?></h3>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Category Breakdown -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold">Incidents by Category</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th class="ps-3">Category</th><th class="text-end pe-3">Ticket Count</th></tr></thead>
                    <tbody>
                        <?php foreach ($categoryCounts as $cat): ?>
                        <tr>
                            <td class="ps-3"><?= e($cat['name']) ?></td>
                            <td class="text-end pe-3 fw-bold"><?= $cat['count'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Department Breakdown -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold">Incidents by Department</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th class="ps-3">Department</th><th class="text-end pe-3">Ticket Count</th></tr></thead>
                    <tbody>
                        <?php foreach ($deptCounts as $dp): ?>
                        <tr>
                            <td class="ps-3"><?= e($dp['name']) ?></td>
                            <td class="text-end pe-3 fw-bold"><?= $dp['count'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Technician Workload and Resolution Time -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="mb-0 fw-bold"><i class="bi bi-people-fill me-2 text-primary"></i>Technician Performance & Resolution Velocity</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Technician Name</th>
                        <th>Assigned Tickets</th>
                        <th>Resolved Tickets</th>
                        <th>Resolution Ratio</th>
                        <th class="text-end pe-3">Avg Resolution Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($techPerformance)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No technician workload records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($techPerformance as $tp): ?>
                        <tr>
                            <td class="ps-3 fw-bold"><?= e($tp['full_name']) ?></td>
                            <td><?= $tp['total_assigned'] ?></td>
                            <td><span class="badge bg-success"><?= $tp['resolved_count'] ?></span></td>
                            <td>
                                <?= $tp['total_assigned'] > 0 ? round(($tp['resolved_count'] / $tp['total_assigned']) * 100, 1) : 0 ?>%
                            </td>
                            <td class="text-end pe-3">
                                <?= !empty($tp['avg_resolution_hours']) ? "{$tp['avg_resolution_hours']} hours" : 'N/A' ?>
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

