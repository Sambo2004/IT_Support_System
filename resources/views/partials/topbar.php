<?php
/**
 * Topbar Navigation Partial
 *
 * Renders the top navigation bar with sidebar toggle, page title,
 * notification bell, and user dropdown menu.
 */
$currentUser = Session::user();
?>

<!-- Top Navigation Bar -->
<nav class="navbar navbar-expand navbar-light bg-white topbar sticky-top border-bottom shadow-sm">
    <div class="container-fluid">

        <!-- Sidebar Toggle Button (Mobile) -->
        <button class="btn btn-link text-dark d-lg-none me-2 sidebar-toggler" type="button"
                id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="bi bi-list fs-3"></i>
        </button>

        <!-- Brand Icon / Title on Mobile -->
        <div class="d-flex align-items-center d-lg-none me-auto">
            <i class="bi bi-headset fs-4 text-primary me-2"></i>
            <span class="fw-bold text-dark fs-6"><?= e(APP_NAME) ?></span>
        </div>

        <!-- Page Title on Desktop -->
        <span class="navbar-text fw-semibold text-dark d-none d-lg-inline fs-5">
            <?= e($pageTitle ?? 'Dashboard') ?>
        </span>

        <!-- Right Side Items -->
        <ul class="navbar-nav ms-auto align-items-center">

            <!-- Notification Bell -->
            <li class="nav-item me-3">
                <a class="nav-link position-relative text-dark" href="<?= url('/notifications') ?>"
                   title="Notifications">
                    <i class="bi bi-bell fs-5"></i>
                    <?php 
                        $unreadCount = (new Notification())->getUnreadCount($currentUser['id'] ?? 0);
                    ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger <?= ($unreadCount > 0) ? '' : 'd-none' ?>"
                          id="unreadNotifBadge" data-url="<?= url('/notifications/count') ?>">
                        <?= $unreadCount ?>
                    </span>
                </a>
            </li>

            <!-- User Dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center text-dark" href="#"
                   id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <!-- User Avatar Circle -->
                    <div class="user-avatar me-2 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 32px; height: 32px; font-size: 14px;">
                        <?= e(strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1))) ?>
                    </div>
                    <span class="d-none d-lg-inline">
                        <?= e($currentUser['full_name'] ?? 'User') ?>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                    <!-- User Info Header -->
                    <li class="dropdown-header">
                        <strong><?= e($currentUser['full_name'] ?? 'User') ?></strong><br>
                        <span class="badge bg-secondary mt-1">
                            <?= e(ucfirst($currentUser['role'] ?? 'unknown')) ?>
                        </span>
                    </li>
                    <li><hr class="dropdown-divider"></li>

                    <!-- My Profile -->
                    <li>
                        <a class="dropdown-item" href="<?= url('/profile') ?>">
                            <i class="bi bi-person-circle me-2 text-primary"></i> My Profile
                        </a>
                    </li>

                    <!-- Change Password -->
                    <li>
                        <a class="dropdown-item" href="<?= url('/change-password') ?>">
                            <i class="bi bi-key me-2 text-warning"></i> Change Password
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>

                    <!-- Logout -->
                    <li>
                        <a class="dropdown-item text-danger" href="<?= url('/logout') ?>">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </li>

        </ul>
    </div>
</nav>
