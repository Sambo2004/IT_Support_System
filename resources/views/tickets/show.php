<?php 
$pageTitle = 'Ticket ' . $ticket['ticket_number']; 
ob_start(); 
$currentUser = Session::user();
?>

<div class="d-flex justify-content-between align-items-center mb-3 page-header-responsive">
    <div>
        <a href="<?= url('/tickets') ?>" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Back to Tickets</a>
        <h4 class="fw-bold mb-0 mt-1">
            <span class="text-primary me-2"><?= e($ticket['ticket_number']) ?></span> <?= e($ticket['title']) ?>
        </h4>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <?= statusBadge($ticket['status']) ?>
        <?= priorityBadge($ticket['priority']) ?>
        <?php if (in_array($currentUser['role'], ['admin', 'technician'])): ?>
            <a href="<?= url('/tickets/' . $ticket['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <!-- Main Ticket Content & Timeline -->
    <div class="col-lg-8">
        <!-- Ticket Description Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="bi bi-file-text me-2 text-primary"></i>Problem Description</h6>
            </div>
            <div class="card-body p-4">
                <p class="mb-0 text-dark" style="white-space: pre-line;"><?= e($ticket['description']) ?></p>

                <?php if (!empty($ticket['resolution'])): ?>
                    <hr class="my-4">
                    <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded">
                        <div class="fw-bold text-success mb-1"><i class="bi bi-check-circle me-1"></i> Resolution Details:</div>
                        <p class="mb-1 text-dark" style="white-space: pre-line;"><?= e($ticket['resolution']) ?></p>
                        <small class="text-muted">Resolved on: <?= formatDateTime($ticket['resolved_at']) ?></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ticket File Attachments -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-paperclip me-2 text-primary"></i>File Attachments (<?= count($attachments ?? []) ?>)</h6>
            </div>
            <div class="card-body p-4">
                <?php if (empty($attachments)): ?>
                    <p class="text-muted small mb-3"><i class="bi bi-info-circle me-1"></i>No attachments uploaded for this ticket yet.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush mb-4 border rounded">
                        <?php foreach ($attachments as $att): ?>
                            <div class="list-group-item d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center py-2 px-3 gap-2">
                                <div class="d-flex align-items-center text-truncate">
                                    <i class="bi bi-file-earmark-arrow-down fs-4 text-primary me-2"></i>
                                    <div class="text-truncate">
                                        <a href="<?= url('/attachments/' . $att['id'] . '/download') ?>" class="fw-semibold text-decoration-none text-dark text-truncate d-block">
                                            <?= e($att['file_name']) ?>
                                        </a>
                                        <small class="text-muted">
                                            <?= number_format($att['file_size'] / 1024, 1) ?> KB &bull; Uploaded by <?= e($att['uploader_name']) ?> (<?= formatDateTime($att['created_at']) ?>)
                                        </small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="<?= url('/attachments/' . $att['id'] . '/download') ?>" class="btn btn-sm btn-outline-primary" title="Download">
                                        <i class="bi bi-download me-1"></i> Download
                                    </a>
                                    <?php if ($currentUser['role'] === 'admin' || (int)$att['user_id'] === (int)$currentUser['id']): ?>
                                        <form action="<?= url('/attachments/' . $att['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Delete this attachment?');" class="d-inline">
                                            <?= Csrf::field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Upload Attachment Form -->
                <form action="<?= url('/tickets/' . $ticket['id'] . '/attachment') ?>" method="POST" enctype="multipart/form-data">
                    <?= Csrf::field() ?>
                    <div class="input-group">
                        <input type="file" name="attachment" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar,.7z,.csv" required>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-upload me-1"></i> Upload File
                        </button>
                    </div>
                    <div class="form-text small mt-1">Allowed: Images, Documents, Spreadsheets, ZIP archives (Max 10MB).</div>
                </form>
            </div>
        </div>

        <!-- Ticket Comments / Conversation -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="bi bi-chat-dots me-2 text-primary"></i>Activity & Comments (<?= count($comments) ?>)</h6>
            </div>
            <div class="card-body p-4">
                <?php if (empty($comments)): ?>
                    <div class="text-center py-4 text-muted small">No comments or activity notes posted yet.</div>
                <?php else: ?>
                    <div class="conversation-stream mb-4">
                        <?php foreach ($comments as $cm): ?>
                            <div class="p-3 mb-3 rounded border <?= $cm['is_internal'] ? 'bg-warning bg-opacity-10 border-warning' : 'bg-light border-light-subtle' ?>">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong class="text-dark"><?= e($cm['author_name']) ?></strong>
                                        <span class="badge bg-secondary ms-1 small"><?= ucfirst($cm['author_role']) ?></span>
                                        <?php if ($cm['is_internal']): ?>
                                            <span class="badge bg-warning text-dark ms-1"><i class="bi bi-lock me-1"></i>Internal Note</span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?= formatDateTime($cm['created_at']) ?></small>
                                </div>
                                <div class="text-dark" style="white-space: pre-line;"><?= e($cm['comment']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Add Comment Form -->
                <form action="<?= url('/tickets/' . $ticket['id'] . '/comment') ?>" method="POST">
                    <?= Csrf::field() ?>
                    <div class="mb-3">
                        <textarea name="comment" class="form-control" rows="3" placeholder="Write a response or follow-up note..." required></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <?php if (in_array($currentUser['role'], ['admin', 'technician'])): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_internal" id="internalCheck" value="1">
                                <label class="form-check-label small text-muted" for="internalCheck">
                                    <i class="bi bi-eye-slash"></i> Visible only to IT Staff (Internal note)
                                </label>
                            </div>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="bi bi-chat-left-text me-1"></i> Post Comment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Audit History -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Audit Trail & Status History</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0 small">
                        <thead>
                            <tr>
                                <th class="ps-3">Date & Time</th>
                                <th>Changed By</th>
                                <th>Field</th>
                                <th>Old Value</th>
                                <th>New Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($history)): ?>
                                <tr><td colspan="5" class="text-center py-3 text-muted">No modification history recorded yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($history as $h): ?>
                                <tr>
                                    <td class="ps-3 text-muted"><?= formatDateTime($h['created_at']) ?></td>
                                    <td><?= e($h['changed_by_name'] ?? 'System') ?></td>
                                    <td><code><?= e($h['field_changed']) ?></code></td>
                                    <td class="text-muted"><?= e($h['old_value'] ?? 'None') ?></td>
                                    <td class="fw-semibold text-dark"><?= e($h['new_value']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Meta & Workflow Controls -->
    <div class="col-lg-4">
        <!-- Quick Ticket Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold">Ticket Information</h6>
            </div>
            <div class="card-body p-3">
                <div class="mb-2 pb-2 border-bottom d-flex justify-content-between">
                    <span class="text-muted small">Requester</span>
                    <strong class="text-dark small"><?= e($ticket['requester_name']) ?></strong>
                </div>
                <div class="mb-2 pb-2 border-bottom d-flex justify-content-between">
                    <span class="text-muted small">Email</span>
                    <span class="small"><?= e($ticket['requester_email']) ?></span>
                </div>
                <div class="mb-2 pb-2 border-bottom d-flex justify-content-between">
                    <span class="text-muted small">Department</span>
                    <span class="small"><?= e($ticket['department_name'] ?? 'Unassigned') ?></span>
                </div>
                <div class="mb-2 pb-2 border-bottom d-flex justify-content-between">
                    <span class="text-muted small">Category</span>
                    <span class="small"><?= e($ticket['category_name']) ?></span>
                </div>
                <div class="mb-2 pb-2 border-bottom d-flex justify-content-between">
                    <span class="text-muted small">Assigned Tech</span>
                    <span class="small fw-semibold <?= empty($ticket['assigned_name']) ? 'text-danger' : 'text-primary' ?>">
                        <?= e($ticket['assigned_name'] ?? 'Unassigned') ?>
                    </span>
                </div>
                <?php if (!empty($ticket['asset_tag'])): ?>
                <div class="mb-2 pb-2 border-bottom d-flex justify-content-between">
                    <span class="text-muted small">Associated Asset</span>
                    <span class="small"><code><?= e($ticket['asset_tag']) ?></code></span>
                </div>
                <?php endif; ?>
                <?php
                $slaHours = ['critical' => 4, 'high' => 8, 'medium' => 24, 'low' => 72][$ticket['priority']] ?? 24;
                $createdTime = strtotime($ticket['created_at']);
                $dueTime = $createdTime + ($slaHours * 3600);
                $isResolved = in_array($ticket['status'], ['resolved', 'closed']);
                $resolvedTime = !empty($ticket['resolved_at']) ? strtotime($ticket['resolved_at']) : ($isResolved ? strtotime($ticket['updated_at']) : null);
                ?>
                <div class="mb-2 pb-2 border-bottom d-flex justify-content-between align-items-center">
                    <span class="text-muted small">SLA Target</span>
                    <span class="small">
                        <?php if ($isResolved): ?>
                            <?php if ($resolvedTime && $resolvedTime <= $dueTime): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>SLA Met (<?= $slaHours ?>h window)</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="bi bi-exclamation-circle me-1"></i>SLA Breached</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if (time() > $dueTime): ?>
                                <span class="badge bg-danger"><i class="bi bi-alarm me-1"></i>Breached (Overdue)</span>
                            <?php elseif (($dueTime - time()) < 7200): ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Due Soon (< 2h)</span>
                            <?php else: ?>
                                <span class="badge bg-info text-dark"><i class="bi bi-clock me-1"></i>On Track (<?= date('M d, H:i', $dueTime) ?>)</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="mb-2 pb-2 border-bottom d-flex justify-content-between">
                    <span class="text-muted small">Submitted Date</span>
                    <span class="small"><?= formatDateTime($ticket['created_at']) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Last Updated</span>
                    <span class="small"><?= formatDateTime($ticket['updated_at']) ?></span>
                </div>
            </div>
        </div>

        <!-- Workflow Action: Status Transition -->
        <?php if (in_array($currentUser['role'], ['admin', 'technician'])): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="bi bi-toggles me-2 text-primary"></i>Update Status</h6>
            </div>
            <div class="card-body p-3">
                <form action="<?= url('/tickets/' . $ticket['id'] . '/status') ?>" method="POST">
                    <?= Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Next Status</label>
                        <select name="status" id="statusSelect" class="form-select form-select-sm" required>
                            <?php foreach (['open', 'assigned', 'in_progress', 'pending', 'resolved', 'closed'] as $st): ?>
                                <option value="<?= $st ?>" <?= ($ticket['status'] === $st) ? 'selected' : '' ?>>
                                    <?= ucwords(str_replace('_', ' ', $st)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3" id="resolutionBlock">
                        <label class="form-label small fw-semibold">Resolution Notes</label>
                        <textarea name="resolution" class="form-control form-control-sm" rows="3" placeholder="Describe actions taken to resolve incident..."><?= e($ticket['resolution'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-sm btn-dark w-100">
                        <i class="bi bi-arrow-repeat me-1"></i> Apply Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Workflow Action: Assign Technician (Admin Only) -->
        <?php if ($currentUser['role'] === 'admin'): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="bi bi-person-gear me-2 text-primary"></i>Assign Technician</h6>
            </div>
            <div class="card-body p-3">
                <form action="<?= url('/tickets/' . $ticket['id'] . '/assign') ?>" method="POST">
                    <?= Csrf::field() ?>
                    <div class="mb-3">
                        <select name="assigned_to" class="form-select form-select-sm" required>
                            <option value="">Choose technician...</option>
                            <?php foreach ($technicians as $tech): ?>
                                <option value="<?= $tech['id'] ?>" <?= ($ticket['assigned_to'] == $tech['id']) ? 'selected' : '' ?>>
                                    <?= e($tech['full_name']) ?> (<?= e($tech['username']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-person-check me-1"></i> Assign to Tech
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require BASE_PATH . '/resources/views/layouts/app.php'; 
?>

