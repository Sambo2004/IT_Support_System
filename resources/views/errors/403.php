<?php
$pageTitle = 'Access Denied';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="text-center p-4">
        <h1 class="display-1 fw-bold text-danger">403</h1>
        <h3 class="fw-semibold text-dark">Access Denied</h3>
        <p class="text-muted">You do not have permission to access this resource or perform this action.</p>
        <a href="<?= url('/') ?>" class="btn btn-primary mt-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Safety
        </a>
    </div>
</body>
</html>

