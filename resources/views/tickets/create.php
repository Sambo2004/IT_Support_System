<?php 
$pageTitle = 'Create Support Ticket'; 
ob_start(); 
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Submit IT Support Ticket</h5>
                <a href="<?= url('/tickets') ?>" class="btn btn-sm btn-outline-secondary">Back to List</a>
            </div>
            <div class="card-body p-4">
                <form action="<?= url('/tickets') ?>" method="POST" enctype="multipart/form-data">
                    <?= Csrf::field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subject / Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="Brief summary of the issue (e.g. Printer offline, cannot open Outlook)" value="<?= e(old('title')) ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (old('category_id') == $cat['id']) ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <option value="low" <?= (old('priority') === 'low') ? 'selected' : '' ?>>Low - Non-urgent inquiry</option>
                                <option value="medium" <?= (old('priority', 'medium') === 'medium') ? 'selected' : '' ?>>Medium - Normal business activity affected</option>
                                <option value="high" <?= (old('priority') === 'high') ? 'selected' : '' ?>>High - Significant work stoppage</option>
                                <option value="critical" <?= (old('priority') === 'critical') ? 'selected' : '' ?>>Critical - Whole department / core system down</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Related Asset (Optional)</label>
                        <select name="asset_id" class="form-select">
                            <option value="">No specific asset or not listed</option>
                            <?php foreach ($assets as $ast): ?>
                                <option value="<?= $ast['id'] ?>" <?= (old('asset_id') == $ast['id']) ? 'selected' : '' ?>>
                                    <?= e($ast['asset_tag']) ?> - <?= e($ast['device_name']) ?> (<?= e($ast['brand'] ?? '') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Choose the equipment encountering problems if applicable.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Detailed Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Describe what happened, error messages, and what you were doing when the issue occurred..." required><?= e(old('description')) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Attachment (Optional)</label>
                        <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar,.7z,.csv">
                        <div class="form-text">Attach screenshots, logs, or diagnostic reports (Max 10MB: JPG, PNG, PDF, Word, Excel, ZIP).</div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                        <a href="<?= url('/tickets') ?>" class="btn btn-light border order-2 order-sm-1">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 order-1 order-sm-2">
                            <i class="bi bi-send me-1"></i> Submit Ticket
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

