<?php
/**
 * Sidebar Navigation Partial
 *
 * Renders the left sidebar with navigation links based on user role.
 * Uses isRole() helper to show/hide menu items.
 * Active link is highlighted based on REQUEST_URI.
 */

// Get current path for active link highlighting
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
// Remove the base path prefix to get relative path (e.g., /IT_Support_System/public/tickets -> /tickets)
$basePath = parse_url(APP_URL, PHP_URL_PATH) ?: '';
$relativePath = str_replace($basePath, '', $currentPath);
// Ensure we have a leading slash
if (empty($relativePath) || $relativePath === '/') {
    $relativePath = '/';
}
?>

<!-- Sidebar -->
<nav id="sidebar" class="sidebar bg-dark">
    <div class="sidebar-inner d-flex flex-column h-100">

        <!-- Brand / Logo -->
        <div class="sidebar-brand d-flex align-items-center justify-content-between px-3 py-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-headset fs-3 text-primary me-2"></i>
                <span class="brand-text text-white fw-bold fs-5"><?= e(APP_NAME) ?></span>
            </div>
            <!-- Close button on mobile -->
            <button class="btn btn-sm btn-link text-white-50 d-lg-none p-0 fs-4" id="sidebarClose" aria-label="Close sidebar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <hr class="text-secondary mx-3 my-0">

        <!-- Navigation Links -->
        <ul class="nav flex-column mt-2 px-2 flex-grow-1">

            <!-- Dashboard - All roles -->
            <li class="nav-item mb-1">
                <a class="nav-link text-white <?= ($relativePath === '/' || $relativePath === '/dashboard') ? 'active' : '' ?>"
                   href="<?= url('/') ?>">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>

            <!-- Tickets - All roles -->
            <li class="nav-item mb-1">
                <a class="nav-link text-white <?= (str_starts_with($relativePath, '/tickets')) ? 'active' : '' ?>"
                   href="<?= url('/tickets') ?>">
                    <i class="bi bi-ticket-detailed me-2"></i> Tickets
                </a>
            </li>

            <!-- Users - Admin only -->
            <?php if (isRole('admin')): ?>
            <li class="nav-item mb-1">
                <a class="nav-link text-white <?= (str_starts_with($relativePath, '/users')) ? 'active' : '' ?>"
                   href="<?= url('/users') ?>">
                    <i class="bi bi-people me-2"></i> Users
                </a>
            </li>
            <?php endif; ?>

            <!-- Departments - Admin only -->
            <?php if (isRole('admin')): ?>
            <li class="nav-item mb-1">
                <a class="nav-link text-white <?= (str_starts_with($relativePath, '/departments')) ? 'active' : '' ?>"
                   href="<?= url('/departments') ?>">
                    <i class="bi bi-building me-2"></i> Departments
                </a>
            </li>
            <?php endif; ?>

            <!-- Categories - Admin only -->
            <?php if (isRole('admin')): ?>
            <li class="nav-item mb-1">
                <a class="nav-link text-white <?= (str_starts_with($relativePath, '/categories')) ? 'active' : '' ?>"
                   href="<?= url('/categories') ?>">
                    <i class="bi bi-tag me-2"></i> Categories
                </a>
            </li>
            <?php endif; ?>

            <!-- Assets - Admin and Technician -->
            <?php if (isRole('admin', 'technician')): ?>
            <li class="nav-item mb-1">
                <a class="nav-link text-white <?= (str_starts_with($relativePath, '/assets')) ? 'active' : '' ?>"
                   href="<?= url('/assets') ?>">
                    <i class="bi bi-pc-display me-2"></i> IT Assets
                </a>
            </li>
            <?php endif; ?>

            <!-- Reports - Admin and Manager only -->
            <?php if (isRole('admin', 'manager')): ?>
            <li class="nav-item mb-1">
                <a class="nav-link text-white <?= (str_starts_with($relativePath, '/reports')) ? 'active' : '' ?>"
                   href="<?= url('/reports') ?>">
                    <i class="bi bi-graph-up me-2"></i> Reports
                </a>
            </li>
            <?php endif; ?>

            <!-- Audit Logs - Admin only -->
            <?php if (isRole('admin')): ?>
            <li class="nav-item mb-1">
                <a class="nav-link text-white <?= (str_starts_with($relativePath, '/audit-logs')) ? 'active' : '' ?>"
                   href="<?= url('/audit-logs') ?>">
                    <i class="bi bi-journal-text me-2"></i> Audit Logs
                </a>
            </li>

            <!-- System Health & Diagnostics - Admin only -->
            <li class="nav-item mb-1">
                <a class="nav-link text-white <?= (str_starts_with($relativePath, '/system/health')) ? 'active' : '' ?>"
                   href="<?= url('/system/health') ?>">
                    <i class="bi bi-heart-pulse me-2"></i> System Health
                </a>
            </li>
            <?php endif; ?>

        </ul>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer px-3 py-3 text-secondary small">
            <i class="bi bi-shield-check me-1"></i>
            <?= e(ucfirst(Session::userRole() ?? 'Guest')) ?>
        </div>

    </div>
</nav>
