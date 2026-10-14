<?php
$pageTitle = '500 - Internal Server Error';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 font-sans">
    <div class="text-center p-4" style="max-width: 500px;">
        <div class="display-1 fw-bold text-danger mb-2">500</div>
        <h3 class="fw-bold text-dark mb-2">Internal Server Error</h3>
        <p class="text-muted mb-4">
            An unexpected error occurred while processing your request. The system administrator has been logged regarding this event.
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="javascript:history.back()" class="btn btn-outline-secondary px-3">
                <i class="bi bi-arrow-left me-1"></i> Go Back
            </a>
            <a href="<?= url('/') ?>" class="btn btn-primary px-3">
                <i class="bi bi-house-door me-1"></i> Dashboard
            </a>
        </div>
    </div>
</body>
</html>

