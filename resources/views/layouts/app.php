<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Dashboard') ?> - <?= e(APP_NAME) ?></title>

    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>

    <div class="d-flex" id="wrapper">

        <!-- Sidebar -->
        <?php require BASE_PATH . '/resources/views/partials/sidebar.php'; ?>

        <!-- Page Content Wrapper -->
        <div id="page-content-wrapper" class="w-100">

            <!-- Topbar -->
            <?php require BASE_PATH . '/resources/views/partials/topbar.php'; ?>

            <!-- Main Content Area -->
            <div class="content-container">

                <!-- Flash Alerts -->
                <?php require BASE_PATH . '/resources/views/partials/alerts.php'; ?>

                <!-- Page Content -->
                <?= $content ?>

            </div>
        </div>
    </div>

    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Bootstrap JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= asset('js/app.js') ?>"></script>

</body>
</html>
