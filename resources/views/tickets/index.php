<?php 
$pageTitle = 'All Tickets'; 
ob_start(); 
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header-responsive">
    <div>
        <h4 class="fw-bold mb-0 text-dark">Support Tickets</h4>
        <p class="text-muted small mb-0">Track and manage all IT requests and incidents</p>
    </div>
    <a href="<?= url('/tickets/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> New Ticket
    </a>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="<?= url('/tickets') ?>" method="GET" class="row g-2 align-items-center filter-form-responsive">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ticket # or title..." value="<?= e($filters['search'] ?? '') ?>">
            </div>

            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php 
                    $statuses = ['open', 'assigned', 'in_progress', 'pending', 'resolved', 'closed'];
                    foreach ($statuses as $st): 
                    ?>
                        <option value="<?= $st ?>" <?= ($filters['status'] === $st) ? 'selected' : '' ?>>
                            <?= ucwords(str_replace('_', ' ', $st)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <select name="priority" class="form-select form-select-sm">
                    <option value="">All Priorities</option>
                    <?php foreach (['low', 'medium', 'high', 'critical'] as $pr): ?>
                        <option value="<?= $pr ?>" <?= ($filters['priority'] === $pr) ? 'selected' : '' ?>><?= ucfirst($pr) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (($filters['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-dark w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                <a href="<?= url('/tickets') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Ticket Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Ticket #</th>
                        <th>Subject</th>
                        <th>Requester</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Technician</th>
                        <th>Submitted</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets)): ?>
                        <tr><td colspan="9" class="text-center py-5 text-muted">No tickets found matching criteria.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td class="ps-3 fw-bold">
                                <a href="<?= url('/tickets/' . $t['id']) ?>" class="text-decoration-none">
                                    <?= e($t['ticket_number']) ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?= url('/tickets/' . $t['id']) ?>" class="text-decoration-none text-dark fw-semibold">
                                    <?= e(mb_strimwidth($t['title'], 0, 45, '...')) ?>
                                </a>
                            </td>
                            <td><?= e($t['requester_name']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= e($t['category_name']) ?></span></td>
                            <td><?= priorityBadge($t['priority']) ?></td>
                            <td>
                                <?= statusBadge($t['status']) ?>
                                <?php
                                $slaHours = ['critical' => 4, 'high' => 8, 'medium' => 24, 'low' => 72][$t['priority']] ?? 24;
                                $isOverdue = !in_array($t['status'], ['resolved', 'closed']) && (time() > (strtotime($t['created_at']) + ($slaHours * 3600)));
                                if ($isOverdue): ?>
                                    <span class="badge bg-danger ms-1" title="SLA Overdue"><i class="bi bi-alarm"></i> Overdue</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($t['assigned_name'] ?? 'Unassigned') ?></td>
                            <td class="small text-muted"><?= formatDate($t['created_at']) ?></td>
                            <td class="text-end pe-3">
                                <a href="<?= url('/tickets/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    View
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
require BASE_PATH . '/resources/views/partials/pagination.php';
$content = ob_get_clean(); 
require BASE_PATH . '/resources/views/layouts/app.php'; 
?>

