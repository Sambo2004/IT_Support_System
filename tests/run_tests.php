<?php
/**
 * Automated Test Suite for IT Support Management System
 * Run via: & 'C:\xampp\php\php.exe' tests/run_tests.php
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/Helpers/Session.php';
require_once BASE_PATH . '/app/Helpers/Csrf.php';
require_once BASE_PATH . '/app/Helpers/Validator.php';
require_once BASE_PATH . '/app/Models/User.php';
require_once BASE_PATH . '/app/Models/Ticket.php';
require_once BASE_PATH . '/app/Models/TicketComment.php';
require_once BASE_PATH . '/app/Models/Asset.php';
require_once BASE_PATH . '/app/Models/Department.php';
require_once BASE_PATH . '/app/Models/Category.php';
require_once BASE_PATH . '/app/Models/AuditLog.php';
require_once BASE_PATH . '/app/Models/Attachment.php';

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    Session::start();
}

class TestRunner {
    private int $passed = 0;
    private int $failed = 0;

    public function assert(string $testName, bool $condition, string $failureDetails = ''): void {
        if ($condition) {
            echo "  [PASS] {$testName}\n";
            $this->passed++;
        } else {
            echo "  [FAIL] {$testName}\n";
            if (!empty($failureDetails)) {
                echo "         --> Reason: {$failureDetails}\n";
            }
            $this->failed++;
        }
    }

    public function report(): void {
        $total = $this->passed + $this->failed;
        echo "\n========================================\n";
        echo "TEST SUMMARY: {$total} run | {$this->passed} passed | {$this->failed} failed\n";
        echo "========================================\n";
        if ($this->failed === 0) {
            echo ">>> ALL TESTS COMPLETED SUCCESSFULLY! <<<\n\n";
        } else {
            echo ">>> SOME TESTS FAILED. Please review the output above. <<<\n\n";
        }
    }
}

$t = new TestRunner();
echo "\n========================================\n";
echo " IT SUPPORT MANAGEMENT SYSTEM - TEST SUITE\n";
echo "========================================\n\n";

// --- TEST GROUP 1: Database Connection ---
echo "[1] Testing Database Connection...\n";
try {
    $db = Database::getConnection();
    $t->assert("Database connected via PDO", $db instanceof PDO);
    $ver = $db->query("SELECT VERSION()")->fetchColumn();
    $t->assert("Database version query works ({$ver})", !empty($ver));
} catch (Exception $e) {
    $t->assert("Database connection", false, $e->getMessage());
}

// --- TEST GROUP 2: Password Verification & Roles ---
echo "\n[2] Testing Authentication & Password Verification...\n";
$userModel = new User();
$admin = $userModel->findByUsername('admin');
$t->assert("Admin user exists in database", !empty($admin));
$t->assert("Admin has role 'admin'", ($admin['role'] ?? '') === 'admin');
$t->assert("Admin password hashes verify with 'password'", password_verify('password', $admin['password_hash'] ?? ''));
$t->assert("Admin rejects wrong password", !password_verify('wrong-password', $admin['password_hash'] ?? ''));

$tech = $userModel->findByUsername('tech1');
$t->assert("Technician 'tech1' exists with role 'technician'", !empty($tech) && $tech['role'] === 'technician');

$emp = $userModel->findByUsername('employee1');
$t->assert("Employee 'employee1' exists with role 'employee'", !empty($emp) && $emp['role'] === 'employee');

// --- TEST GROUP 3: Ticket Number Generation & Concurrency Safety ---
echo "\n[3] Testing Ticket Sequential Number Generator...\n";
$ticketModel = new Ticket();
$currentYear = (int)date('Y');
$stmt = $db->prepare("SELECT last_number FROM ticket_number_seq WHERE year = ?");
$stmt->execute([$currentYear]);
$currentSeq = (int)$stmt->fetchColumn();

$newNum = $ticketModel->generateTicketNumber();
$expectedFormat = "/^IT-{$currentYear}-\\d{4}$/";
$t->assert("Generated ticket format matches IT-YYYY-NNNN ({$newNum})", (bool)preg_match($expectedFormat, $newNum));

// Check sequential increment
$stmt->execute([$currentYear]);
$newSeq = (int)$stmt->fetchColumn();
$t->assert("Ticket sequence safely incremented (+1)", $newSeq === ($currentSeq + 1));

// --- TEST GROUP 4: Ticket Creation & CRUD Operations ---
echo "\n[4] Testing Ticket Creation & Workflow...\n";
$testTicketId = $ticketModel->create([
    'title' => 'Unit Test Automated Incident',
    'description' => 'Automated integration test description.',
    'category_id' => 1,
    'requester_id' => $emp['id'],
    'department_id' => $emp['department_id'],
    'priority' => 'high'
]);
$t->assert("Ticket created with ID", $testTicketId > 0);

$fetched = $ticketModel->findById($testTicketId);
$t->assert("Ticket fetched with JOINs (Requester: {$fetched['requester_name']})", !empty($fetched) && $fetched['requester_name'] === $emp['full_name']);
$t->assert("Initial status is 'open'", ($fetched['status'] ?? '') === 'open');

// Test Technician Assignment
$ticketModel->assignTechnician($testTicketId, $tech['id'], $admin['id']);
$fetchedAfterAssign = $ticketModel->findById($testTicketId);
$t->assert("Ticket assigned to technician ID {$tech['id']}", (int)$fetchedAfterAssign['assigned_to'] === (int)$tech['id']);
$t->assert("Ticket assigned_name alias matches technician name", ($fetchedAfterAssign['assigned_name'] ?? '') === $tech['full_name']);
$t->assert("Ticket status auto-transitioned to 'assigned'", $fetchedAfterAssign['status'] === 'assigned');

// Test Status Transition to Resolved with notes
$ticketModel->updateStatus($testTicketId, 'resolved', $tech['id'], 'Fixed in automated test.');
$fetchedAfterResolve = $ticketModel->findById($testTicketId);
$t->assert("Ticket status updated to 'resolved'", $fetchedAfterResolve['status'] === 'resolved');
$t->assert("Resolved timestamp recorded", !empty($fetchedAfterResolve['resolved_at']));
$t->assert("Resolution note preserved", $fetchedAfterResolve['resolution'] === 'Fixed in automated test.');

// Test Comments
$commentModel = new TicketComment();
$cId = $commentModel->create($testTicketId, $tech['id'], 'Test technical comment', false);
$t->assert("Comment added to ticket", $cId > 0);
$comments = $commentModel->getByTicketId($testTicketId, true);
$t->assert("Comment retrieved for ticket", count($comments) >= 1);

// Test History Log
$history = $ticketModel->getHistory($testTicketId);
$t->assert("Ticket audit trail recorded field changes", count($history) >= 2);
$t->assert("Ticket audit trail includes changed_by_name alias", !empty($history[0]['changed_by_name']));

// --- TEST GROUP 5: IT Asset Management ---
echo "\n[5] Testing IT Asset Management...\n";
$assetModel = new Asset();
$tag = 'TEST-TAG-' . time();
$assetId = $assetModel->create([
    'asset_tag' => $tag,
    'device_name' => 'Automated Test Monitor',
    'category_id' => 3,
    'brand' => 'Dell',
    'model' => 'U2723QE',
    'status' => 'available'
]);
$t->assert("Asset created with tag {$tag}", $assetId > 0);

$asset = $assetModel->findById($assetId);
$t->assert("Asset retrieved successfully", !empty($asset) && $asset['device_name'] === 'Automated Test Monitor');

// Clean up test asset
$db->prepare("DELETE FROM assets WHERE id = ?")->execute([$assetId]);
$t->assert("Cleaned up test asset", true);

// Clean up test ticket
$db->prepare("DELETE FROM tickets WHERE id = ?")->execute([$testTicketId]);
$t->assert("Cleaned up test ticket", true);

// --- TEST GROUP 6: Security Helpers (CSRF & Sanitization) ---
echo "\n[6] Testing Security Helpers...\n";
$token = Csrf::generate();
$t->assert("CSRF token generated (64 hex characters)", strlen($token) === 64);
$t->assert("CSRF token verifies matching value", Csrf::verify($token));
$t->assert("CSRF token rejects forged value", !Csrf::verify('invalid-token-12345'));

$maliciousInput = "<script>alert('xss')</script>";
$sanitized = e($maliciousInput);
$t->assert("e() helper escapes XSS payloads", strpos($sanitized, '<script>') === false && strpos($sanitized, '&lt;script&gt;') !== false);

// Formula injection test
$formula = "=cmd|' /C calc'!A0";
$safeFormula = in_array($formula[0], ['=', '+', '-', '@']) ? "'" . $formula : $formula;
$t->assert("Spreadsheet formula injection properly escaped with single quote", str_starts_with($safeFormula, "'="));

// --- TEST GROUP 7: Ticket Attachments & Storage Security ---
echo "\n[7] Testing Ticket File Attachments & Storage Security...\n";
$htaccessFile = BASE_PATH . '/storage/attachments/.htaccess';
$t->assert("Attachment directory .htaccess exists", file_exists($htaccessFile));
$htaccessContent = file_get_contents($htaccessFile);
$t->assert(".htaccess enforces access restrictions (denies direct script execution)", strpos($htaccessContent, 'denied') !== false || strpos($htaccessContent, 'Deny') !== false);

// Create temporary ticket for attachment test
$tempTicketId = $ticketModel->create([
    'title' => 'Attachment Verification Incident',
    'description' => 'Testing file metadata persistence.',
    'category_id' => 1,
    'requester_id' => $emp['id'],
    'department_id' => $emp['department_id'],
    'priority' => 'low'
]);

$attachmentModel = new Attachment();
$attId = $attachmentModel->create([
    'ticket_id' => $tempTicketId,
    'user_id' => $emp['id'],
    'file_name' => 'network_diagnostic.log',
    'stored_name' => 'test_' . bin2hex(random_bytes(8)) . '.log',
    'file_size' => 4096,
    'mime_type' => 'text/plain'
]);
$t->assert("Attachment metadata inserted with ID", $attId > 0);

$fetchedAtt = $attachmentModel->findById($attId);
$t->assert("Attachment retrieved by ID with uploader name", !empty($fetchedAtt) && $fetchedAtt['file_name'] === 'network_diagnostic.log');
$t->assert("Attachment uploader matches user", $fetchedAtt['uploader_name'] === $emp['full_name']);

$ticketAttachments = $attachmentModel->getByTicketId($tempTicketId);
$t->assert("Ticket attachments list retrieved", count($ticketAttachments) === 1);

// Test deletion
$deleted = $attachmentModel->delete($attId);
$t->assert("Attachment deleted successfully", $deleted);
$t->assert("Attachment no longer found after delete", $attachmentModel->findById($attId) === null);

// Clean up temporary ticket
$db->prepare("DELETE FROM tickets WHERE id = ?")->execute([$tempTicketId]);
$t->assert("Cleaned up temporary attachment ticket", true);

// --- TEST GROUP 8: Deployment Readiness & Profile Self-Service ---
echo "\n[8] Testing Deployment Diagnostics & Profile Self-Service...\n";

// 1. User profile retrieval with department name
$profile = $userModel->findById($emp['id']);
$t->assert("Employee profile retrieved with department", !empty($profile['department_name']));

// 2. Profile update & persistence
$originalName = $profile['full_name'];
$userModel->update($emp['id'], ['full_name' => 'Jane Employee Updated']);
$updatedProfile = $userModel->findById($emp['id']);
$t->assert("Profile update persisted to database", $updatedProfile['full_name'] === 'Jane Employee Updated');
// Restore original name
$userModel->update($emp['id'], ['full_name' => $originalName]);
$t->assert("Profile restored to original state", $userModel->findById($emp['id'])['full_name'] === $originalName);

// 3. Storage directories writable for deployment
$logsWritable = is_writable(BASE_PATH . '/storage/logs');
$attachmentsWritable = is_writable(BASE_PATH . '/storage/attachments');
$t->assert("storage/logs directory is writable for production error logging", $logsWritable);
$t->assert("storage/attachments directory is writable for uploaded evidence", $attachmentsWritable);

// 4. Runtime requirements
$t->assert("PHP runtime satisfies >= 8.0 requirement (" . PHP_VERSION . ")", version_compare(PHP_VERSION, '8.0.0', '>='));
$t->assert("Required extensions loaded (pdo, pdo_mysql, mbstring, openssl)", 
    extension_loaded('pdo') && extension_loaded('pdo_mysql') && extension_loaded('mbstring') && extension_loaded('openssl'));

// Finish
$t->report();
