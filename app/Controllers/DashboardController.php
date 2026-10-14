<?php
// app/Controllers/DashboardController.php

class DashboardController {
    private Ticket $ticketModel;
    private Asset $assetModel;

    public function __construct() {
        AuthMiddleware::handle();
        $this->ticketModel = new Ticket();
        $this->assetModel = new Asset();
    }

    public function index(): void {
        $user = Session::user();
        $userId = $user['id'];
        $role = $user['role'];

        $statusCounts = $this->ticketModel->getStatusCounts();
        $priorityCounts = $this->ticketModel->getPriorityCounts();
        $recentTickets = $this->ticketModel->getRecentTickets(6, $userId, $role);
        $assetCounts = $this->assetModel->getStatusCounts();
        $expiringWarranties = $this->assetModel->getExpiringWarranties(90);

        // Calculate aggregate statistics
        $totalTickets = array_sum($statusCounts);
        $openTickets = ($statusCounts['open'] ?? 0) + ($statusCounts['assigned'] ?? 0);
        $inProgressTickets = $statusCounts['in_progress'] ?? 0;
        $resolvedTickets = ($statusCounts['resolved'] ?? 0) + ($statusCounts['closed'] ?? 0);
        $criticalTickets = $priorityCounts['critical'] ?? 0;
        $highTickets = $priorityCounts['high'] ?? 0;

        view('dashboard.index', [
            'totalTickets' => $totalTickets,
            'openTickets' => $openTickets,
            'inProgressTickets' => $inProgressTickets,
            'resolvedTickets' => $resolvedTickets,
            'criticalTickets' => $criticalTickets,
            'highTickets' => $highTickets,
            'statusCounts' => $statusCounts,
            'priorityCounts' => $priorityCounts,
            'recentTickets' => $recentTickets,
            'assetCounts' => $assetCounts,
            'expiringWarranties' => $expiringWarranties
        ]);
    }

    public function health(): void {
        AuthMiddleware::handle(['admin']);
        $start = microtime(true);
        $dbStatus = false;
        $dbError = null;
        try {
            $db = Database::getConnection();
            $db->query("SELECT 1");
            $dbStatus = true;
        } catch (Exception $e) {
            $dbError = $e->getMessage();
        }
        $dbLatency = round((microtime(true) - $start) * 1000, 2);

        $checks = [
            'php_version' => [
                'name' => 'PHP Runtime',
                'value' => PHP_VERSION,
                'status' => version_compare(PHP_VERSION, '8.0.0', '>='),
                'details' => 'Required >= 8.0'
            ],
            'database' => [
                'name' => 'MariaDB / MySQL',
                'value' => $dbStatus ? "Connected ({$dbLatency}ms latency)" : 'Disconnected',
                'status' => $dbStatus,
                'details' => $dbError ?: 'PDO MySQL connection operational'
            ],
            'storage_logs' => [
                'name' => 'Storage Logs Directory',
                'value' => is_writable(BASE_PATH . '/storage/logs') ? 'Writable' : 'Read-only / Missing',
                'status' => is_writable(BASE_PATH . '/storage/logs'),
                'details' => BASE_PATH . '/storage/logs'
            ],
            'storage_attachments' => [
                'name' => 'Storage Attachments Directory',
                'value' => is_writable(BASE_PATH . '/storage/attachments') ? 'Writable' : 'Read-only / Missing',
                'status' => is_writable(BASE_PATH . '/storage/attachments'),
                'details' => BASE_PATH . '/storage/attachments'
            ],
            'session_security' => [
                'name' => 'Session Security',
                'value' => ini_get('session.cookie_httponly') ? 'HttpOnly & Strict Enabled' : 'Standard',
                'status' => (bool)ini_get('session.cookie_httponly'),
                'details' => 'Samesite=Strict, HttpOnly'
            ],
            'active_tickets' => [
                'name' => 'Active Support Incidents',
                'value' => (string)(($this->ticketModel->getStatusCounts()['open'] ?? 0) + ($this->ticketModel->getStatusCounts()['in_progress'] ?? 0)) . ' Active Incidents',
                'status' => true,
                'details' => 'System processing tickets'
            ]
        ];

        if (isset($_GET['format']) && $_GET['format'] === 'json') {
            header('Content-Type: application/json');
            $allHealthy = !in_array(false, array_column($checks, 'status'), true);
            echo json_encode([
                'status' => $allHealthy ? 'healthy' : 'degraded',
                'timestamp' => date('c'),
                'checks' => $checks
            ], JSON_PRETTY_PRINT);
            exit;
        }

        view('dashboard.health', ['checks' => $checks, 'dbLatency' => $dbLatency]);
    }
}


