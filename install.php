<?php
/**
 * IT Support Management System - Web-based Automated Installer
 * Accessible via: http://localhost/IT_Support_System/install.php
 */

session_start();
$basePath = __DIR__;
$envFile = $basePath . '/.env';

// System Requirements Check
$requirements = [
    'PHP >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
    'PDO Extension' => extension_loaded('pdo'),
    'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
    'Mbstring Extension' => extension_loaded('mbstring'),
    'OpenSSL Extension' => extension_loaded('openssl'),
    'storage/ writable' => is_writable($basePath . '/storage') || (!file_exists($basePath . '/storage') && is_writable($basePath)),
];
$allRequirementsMet = !in_array(false, $requirements, true);

// Check if already installed
$isInstalled = false;
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    if (preg_match('/DB_DATABASE=(.+)/', $envContent, $matches)) {
        $dbName = trim($matches[1], "\"' \r\n");
        if (!empty($dbName)) {
            try {
                preg_match('/DB_HOST=(.+)/', $envContent, $mHost);
                preg_match('/DB_PORT=(.+)/', $envContent, $mPort);
                preg_match('/DB_USERNAME=(.+)/', $envContent, $mUser);
                preg_match('/DB_PASSWORD=(.*)/', $envContent, $mPass);

                $host = !empty($mHost[1]) ? trim($mHost[1], "\"' \r\n") : 'localhost';
                $port = !empty($mPort[1]) ? trim($mPort[1], "\"' \r\n") : '3306';
                $user = !empty($mUser[1]) ? trim($mUser[1], "\"' \r\n") : 'root';
                $pass = isset($mPass[1]) ? trim($mPass[1], "\"' \r\n") : '';

                $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4", $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
                $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
                if ($stmt->fetch()) {
                    $cnt = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                    if ($cnt > 0) {
                        $isInstalled = true;
                    }
                }
            } catch (Exception $e) {
                // Connection failed or database does not exist yet
            }
        }
    }
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $allRequirementsMet) {
    // Simple CSRF check
    $postToken = $_POST['_installer_token'] ?? '';
    if (empty($_SESSION['_installer_token']) || !hash_equals($_SESSION['_installer_token'], $postToken)) {
        $error = 'Invalid installer session token. Please reload and try again.';
    } else {
        $dbHost = trim($_POST['db_host'] ?? 'localhost');
        $dbPort = trim($_POST['db_port'] ?? '3306');
        $dbName = trim($_POST['db_name'] ?? 'it_support');
        $dbUser = trim($_POST['db_user'] ?? 'root');
        $dbPass = $_POST['db_pass'] ?? '';

        $adminName = trim($_POST['admin_name'] ?? 'System Administrator');
        $adminUser = trim($_POST['admin_user'] ?? 'admin');
        $adminEmail = trim($_POST['admin_email'] ?? 'admin@itsupport.local');
        $adminPass = $_POST['admin_pass'] ?? '';
        $appUrl = rtrim(trim($_POST['app_url'] ?? 'http://localhost/IT_Support_System/public'), '/');
        $seedDevData = !empty($_POST['seed_dev_data']);
        $forceReinstall = !empty($_POST['force_reinstall']);

        if (empty($dbHost) || empty($dbName) || empty($dbUser)) {
            $error = 'Database configuration fields cannot be empty.';
        } elseif (empty($adminUser) || empty($adminEmail) || strlen($adminPass) < 8) {
            $error = 'Admin user, valid email, and password (minimum 8 characters) are required.';
        } elseif ($isInstalled && !$forceReinstall) {
            $error = 'The system is already installed. Check "Confirm Reinstallation" if you wish to overwrite existing data.';
        } else {
            try {
                // Connect to MySQL server
                $rootPdo = new PDO("mysql:host={$dbHost};port={$dbPort};charset=utf8mb4", $dbUser, $dbPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);

                // Create database
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $rootPdo->exec("USE `{$dbName}`");

                // Execute schema.sql
                $schemaFile = $basePath . '/database/schema.sql';
                if (!file_exists($schemaFile)) {
                    throw new Exception("Schema file not found at: {$schemaFile}");
                }
                $schemaSql = file_get_contents($schemaFile);
                $rootPdo->exec($schemaSql);

                // Check or insert admin user
                $hash = password_hash($adminPass, PASSWORD_BCRYPT);
                $stmt = $rootPdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$adminUser, $adminEmail]);
                $existingAdmin = $stmt->fetch();

                if ($existingAdmin) {
                    $upd = $rootPdo->prepare("UPDATE users SET password_hash = ?, full_name = ?, role = 'admin', is_active = 1 WHERE id = ?");
                    $upd->execute([$hash, $adminName, $existingAdmin['id']]);
                } else {
                    $ins = $rootPdo->prepare("
                        INSERT INTO users (username, email, password_hash, full_name, role, is_active, created_at)
                        VALUES (?, ?, ?, ?, 'admin', 1, NOW())
                    ");
                    $ins->execute([$adminUser, $adminEmail, $hash, $adminName]);
                }

                // Optionally seed development records
                if ($seedDevData) {
                    $seedFile = $basePath . '/database/seed.sql';
                    if (file_exists($seedFile)) {
                        $seedSql = file_get_contents($seedFile);
                        // Clean out any conflicting admin insertion from seed if existing
                        $rootPdo->exec($seedSql);
                        // Re-ensure custom admin password
                        $upd = $rootPdo->prepare("UPDATE users SET password_hash = ?, full_name = ?, is_active = 1 WHERE username = ?");
                        $upd->execute([$hash, $adminName, $adminUser]);
                    }
                }

                // Write .env
                $envData = <<<ENV
APP_NAME="IT Support Management System"
APP_ENV=production
APP_DEBUG=false
APP_URL="{$appUrl}"
APP_TIMEZONE=Asia/Phnom_Penh

DB_HOST={$dbHost}
DB_PORT={$dbPort}
DB_DATABASE={$dbName}
DB_USERNAME={$dbUser}
DB_PASSWORD="{$dbPass}"

SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=false
ENV;
                file_put_contents($envFile, $envData);

                // Ensure directories exist
                @mkdir($basePath . '/storage/logs', 0755, true);
                @mkdir($basePath . '/storage/attachments', 0755, true);

                $success = true;
            } catch (Exception $ex) {
                $error = 'Installation Error: ' . $ex->getMessage();
            }
        }
    }
}

// Generate new token
$_SESSION['_installer_token'] = bin2hex(random_bytes(32));
$defaultAppUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['SCRIPT_NAME']) . '/public';
$defaultAppUrl = str_replace('\\', '/', $defaultAppUrl);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - IT Support Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; }
        .install-card { max-width: 680px; margin: 40px auto; border: none; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .header-box { background: linear-gradient(135deg, #1e293b, #0f172a); color: white; border-radius: 12px 12px 0 0; padding: 25px 30px; }
    </style>
</head>
<body>
<div class="container pb-5">
    <div class="card install-card">
        <div class="header-box text-center">
            <h3 class="fw-bold mb-1"><i class="bi bi-headset me-2 text-primary"></i>IT Support System</h3>
            <p class="text-white-50 mb-0 small">Automated Setup & Environment Installer</p>
        </div>
        <div class="card-body p-4 p-md-5">

            <?php if ($success): ?>
                <div class="alert alert-success p-4 text-center">
                    <i class="bi bi-check-circle-fill fs-1 text-success d-block mb-3"></i>
                    <h4 class="fw-bold alert-heading">Installation Completed Successfully!</h4>
                    <p class="mb-3">The database tables have been initialized, the administrator profile configured, and <code>.env</code> generated.</p>
                    <hr>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <a href="<?= htmlspecialchars($appUrl) ?>/login" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Proceed to Login
                        </a>
                    </div>
                </div>
            <?php else: ?>

                <?php if (!$allRequirementsMet): ?>
                    <div class="alert alert-danger mb-4">
                        <h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Server Requirements Missing:</h6>
                        <ul class="mb-0 small">
                            <?php foreach ($requirements as $name => $met): ?>
                                <li><?= htmlspecialchars($name) ?>: <strong><?= $met ? '<span class="text-success">PASS</span>' : '<span class="text-danger">FAIL</span>' ?></strong></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($isInstalled): ?>
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        <strong>Existing Installation Detected:</strong> Database contains existing accounts. If you proceed, existing tables may be updated or reset. Check "Confirm Reinstallation" below to proceed.
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger mb-4 alert-dismissible fade show">
                        <i class="bi bi-x-circle-fill me-1"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="install.php">
                    <input type="hidden" name="_installer_token" value="<?= htmlspecialchars($_SESSION['_installer_token']) ?>">

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-database me-2 text-primary"></i>Database Connection</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Database Host</label>
                            <input type="text" name="db_host" class="form-control" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Port</label>
                            <input type="text" name="db_port" class="form-control" value="<?= htmlspecialchars($_POST['db_port'] ?? '3306') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Database Name</label>
                            <input type="text" name="db_name" class="form-control" value="<?= htmlspecialchars($_POST['db_name'] ?? 'it_support') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">DB Username</label>
                            <input type="text" name="db_user" class="form-control" value="<?= htmlspecialchars($_POST['db_user'] ?? 'root') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">DB Password</label>
                            <input type="password" name="db_pass" class="form-control" placeholder="Leave empty if none">
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-person-badge me-2 text-primary"></i>Primary Administrator</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Admin Full Name</label>
                            <input type="text" name="admin_name" class="form-control" value="<?= htmlspecialchars($_POST['admin_name'] ?? 'System Administrator') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Admin Username</label>
                            <input type="text" name="admin_user" class="form-control" value="<?= htmlspecialchars($_POST['admin_user'] ?? 'admin') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Admin Email</label>
                            <input type="email" name="admin_email" class="form-control" value="<?= htmlspecialchars($_POST['admin_email'] ?? 'admin@itsupport.local') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Admin Password (min 8 chars)</label>
                            <input type="password" name="admin_pass" class="form-control" placeholder="Min 8 characters" required>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-gear me-2 text-primary"></i>System Settings</h5>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Application Base URL</label>
                        <input type="url" name="app_url" class="form-control" value="<?= htmlspecialchars($_POST['app_url'] ?? $defaultAppUrl) ?>" required>
                        <div class="form-text small">Points to your public/ directory.</div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="seed_dev_data" class="form-check-input" id="seedCheck" value="1" <?= isset($_POST['seed_dev_data']) ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="seedCheck">
                            Install Sample Seed Data (Technicians, Sample Tickets, IT Categories, Departments)
                        </label>
                    </div>

                    <?php if ($isInstalled): ?>
                    <div class="mb-4 form-check bg-danger bg-opacity-10 p-3 rounded border border-danger">
                        <input type="checkbox" name="force_reinstall" class="form-check-input ms-0 me-2" id="reinstallCheck" value="1" required>
                        <label class="form-check-label small fw-bold text-danger" for="reinstallCheck">
                            I understand and confirm reinstallation on this database.
                        </label>
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" <?= !$allRequirementsMet ? 'disabled' : '' ?>>
                        <i class="bi bi-rocket-takeoff me-1"></i> Start Installation & Initialize System
                    </button>
                </form>
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>

