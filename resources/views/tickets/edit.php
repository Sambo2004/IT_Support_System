<?php 
$pageTitle = 'Edit Ticket ' . $ticket['ticket_number']; 
ob_start(); 
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Edit Ticket: <?= e($ticket['ticket_number']) ?></h5>
                <a href="<?= url('/tickets/' . $ticket['id']) ?>" class="btn btn-sm btn-outline-secondary">Cancel</a>
            </div>
            <div class="card-body p-4">
                <form action="<?= url('/tickets/' . $ticket['id']) ?>" method="POST">
                    <?= Csrf::field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subject / Title</label>
                        <input type="text" name="title" class="form-control" value="<?= e($ticket['title']) ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($ticket['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Priority</label>
                            <select name="priority" class="form-select" required>
                                <?php foreach (['low', 'medium', 'high', 'critical'] as $pr): ?>
                                    <option value="<?= $pr ?>" <?= ($ticket['priority'] === $pr) ? 'selected' : '' ?>>
                                        <?= ucfirst($pr) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="6" required><?= e($ticket['description']) ?></textarea>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                        <a href="<?= url('/tickets/' . $ticket['id']) ?>" class="btn btn-light border order-2 order-sm-1">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 order-1 order-sm-2">
                            <i class="bi bi-save me-1"></i> Update Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require BASE_PATH . '/resources/views/layouts/app.php'; 
?>

