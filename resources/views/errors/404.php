<?php
$pageTitle = 'Page Not Found';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="text-center p-4">
        <h1 class="display-1 fw-bold text-secondary">404</h1>
        <h3 class="fw-semibold text-dark">Page Not Found</h3>
        <p class="text-muted">The page you are looking for does not exist or has been moved.</p>
        <a href="<?= url('/') ?>" class="btn btn-primary mt-2">
            <i class="bi bi-house-door me-1"></i> Return to Dashboard
        </a>
    </div>
</body>
</html>

