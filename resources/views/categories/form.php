<?php 
$isEdit = !empty($category);
$pageTitle = $isEdit ? 'Edit Category' : 'Create Category'; 
ob_start(); 
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><?= $pageTitle ?></h5>
                <a href="<?= url('/categories') ?>" class="btn btn-sm btn-outline-secondary">Back to List</a>
            </div>
            <div class="card-body p-4">
                <form action="<?= $isEdit ? url('/categories/' . $category['id']) : url('/categories') ?>" method="POST">
                    <?= Csrf::field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= e($category['name'] ?? old('name')) ?>" placeholder="e.g. Network & Connectivity" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Description of typical issues under this category..."><?= e($category['description'] ?? old('description')) ?></textarea>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="activeCheck" value="1" <?= (!$isEdit || !empty($category['is_active'])) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="activeCheck">Active (Selectable on new ticket forms)</label>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                        <a href="<?= url('/categories') ?>" class="btn btn-light border order-2 order-sm-1">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 order-1 order-sm-2"><?= $isEdit ? 'Update' : 'Create' ?></button>
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

