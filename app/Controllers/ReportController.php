<?php
// app/Controllers/ReportController.php

class ReportController {
    private PDO $db;

    public function __construct() {
        AuthMiddleware::handle(['admin', 'manager']);
        $this->db = Database::getConnection();
    }

    public function index(): void {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');

        // Tickets by Status in date range
        $stmt = $this->db->prepare("SELECT status, COUNT(*) as count FROM tickets WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY status");
        $stmt->execute([$from, $to]);
        $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Tickets by Priority in date range
        $stmt = $this->db->prepare("SELECT priority, COUNT(*) as count FROM tickets WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY priority");
        $stmt->execute([$from, $to]);
        $priorityCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Tickets by Category
        $stmt = $this->db->prepare("SELECT c.name, COUNT(t.id) as count 
                                     FROM ticket_categories c 
                                     LEFT JOIN tickets t ON c.id = t.category_id AND DATE(t.created_at) BETWEEN ? AND ? 
                                     GROUP BY c.id ORDER BY count DESC");
        $stmt->execute([$from, $to]);
        $categoryCounts = $stmt->fetchAll();

        // Tickets by Department
        $stmt = $this->db->prepare("SELECT d.name, COUNT(t.id) as count 
                                     FROM departments d 
                                     LEFT JOIN tickets t ON d.id = t.department_id AND DATE(t.created_at) BETWEEN ? AND ? 
                                     GROUP BY d.id ORDER BY count DESC");
        $stmt->execute([$from, $to]);
        $deptCounts = $stmt->fetchAll();

        // Technician resolution performance
        $stmt = $this->db->prepare("SELECT u.full_name, 
                                            COUNT(t.id) as total_assigned,
                                            SUM(CASE WHEN t.status IN ('resolved', 'closed') THEN 1 ELSE 0 END) as resolved_count,
                                            ROUND(AVG(CASE WHEN t.resolved_at IS NOT NULL THEN TIMESTAMPDIFF(HOUR, t.created_at, t.resolved_at) ELSE NULL END), 1) as avg_resolution_hours
                                     FROM users u 
                                     LEFT JOIN tickets t ON u.id = t.assigned_to AND DATE(t.created_at) BETWEEN ? AND ?
                                     WHERE u.role = 'technician' AND u.is_active = 1
                                     GROUP BY u.id");
        $stmt->execute([$from, $to]);
        $techPerformance = $stmt->fetchAll();

        $totalTickets = array_sum($statusCounts);
        $resolvedCount = ($statusCounts['resolved'] ?? 0) + ($statusCounts['closed'] ?? 0);

        view('reports.index', [
            'from' => $from,
            'to' => $to,
            'totalTickets' => $totalTickets,
            'resolvedCount' => $resolvedCount,
            'statusCounts' => $statusCounts,
            'priorityCounts' => $priorityCounts,
            'categoryCounts' => $categoryCounts,
            'deptCounts' => $deptCounts,
            'techPerformance' => $techPerformance
        ]);
    }

    public function export(): void {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');

        $stmt = $this->db->prepare("SELECT t.ticket_number, t.title, c.name as category, 
                                            u.full_name as requester, d.name as department, 
                                            tech.full_name as assigned_to, t.priority, t.status, 
                                            t.created_at, t.resolved_at 
                                     FROM tickets t 
                                     LEFT JOIN ticket_categories c ON t.category_id = c.id 
                                     LEFT JOIN users u ON t.requester_id = u.id 
                                     LEFT JOIN departments d ON t.department_id = d.id 
                                     LEFT JOIN users tech ON t.assigned_to = tech.id 
                                     WHERE DATE(t.created_at) BETWEEN ? AND ? 
                                     ORDER BY t.created_at DESC");
        $stmt->execute([$from, $to]);
        $tickets = $stmt->fetchAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=tickets_report_' . date('Ymd_His') . '.csv');

        $output = fopen('php://output', 'w');
        // Excel UTF-8 BOM
        fputs($output, "\xEF\xBB\xBF");

        fputcsv($output, ['Ticket #', 'Title', 'Category', 'Requester', 'Department', 'Assigned To', 'Priority', 'Status', 'Created At', 'Resolved At']);

        foreach ($tickets as $row) {
            // Formula injection prevention
            foreach ($row as $k => $val) {
                if (is_string($val) && strlen($val) > 0 && in_array($val[0], ['=', '+', '-', '@'])) {
                    $row[$k] = "'" . $val;
                }
            }
            fputcsv($output, [
                $row['ticket_number'],
                $row['title'],
                $row['category'],
                $row['requester'],
                $row['department'],
                $row['assigned_to'] ?? 'Unassigned',
                ucfirst($row['priority']),
                ucfirst(str_replace('_', ' ', $row['status'])),
                $row['created_at'],
                $row['resolved_at'] ?? 'N/A'
            ]);
        }
        fclose($output);
        exit;
    }
}

