<?php
/**
 * Flash Alerts Partial
 *
 * Displays flash messages from Flash::get() and validation errors
 * from $_SESSION['errors']. Messages are shown as Bootstrap alerts
 * that auto-dismiss after 5 seconds (handled by app.js).
 */

// Display flash messages
$flashMessages = Flash::get();
foreach ($flashMessages as $flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?php if ($flash['type'] === 'success'): ?>
            <i class="bi bi-check-circle-fill me-2"></i>
        <?php elseif ($flash['type'] === 'danger'): ?>
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php elseif ($flash['type'] === 'warning'): ?>
            <i class="bi bi-exclamation-circle-fill me-2"></i>
        <?php elseif ($flash['type'] === 'info'): ?>
            <i class="bi bi-info-circle-fill me-2"></i>
        <?php endif; ?>
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endforeach; ?>

<?php
// Display validation errors from $_SESSION['errors']
if (!empty($_SESSION['errors']) && is_array($_SESSION['errors'])):
?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($_SESSION['errors'] as $field => $messages): ?>
                <?php if (is_array($messages)): ?>
                    <?php foreach ($messages as $message): ?>
                        <li><?= e($message) ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li><?= e($messages) ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php
    // Clear validation errors after displaying
    unset($_SESSION['errors']);
    unset($_SESSION['validation_errors']);
endif;
?>
