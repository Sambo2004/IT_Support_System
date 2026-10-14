<?php
// app/Models/AuditLog.php

class AuditLog {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public static function record(?int $userId, string $action, string $entityType, ?int $entityId = null, ?string $details = null): void {
        try {
            $db = Database::getConnection();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $action, $entityType, $entityId, $details, $ip]);
        } catch (Exception $e) {
            // Silently ignore log failures in execution
        }
    }

    public function log(?int $userId, string $action, string $entityType, ?int $entityId = null, ?string $details = null): void {
        self::record($userId, $action, $entityType, $entityId, $details);
    }

    public function getAll(int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT a.*, u.username, u.full_name 
                FROM audit_logs a 
                LEFT JOIN users u ON a.user_id = u.id 
                ORDER BY a.created_at DESC 
                LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
        $stmt = $this->db->query($sql);
        $logs = $stmt->fetchAll();

        $total = (int)$this->db->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();

        return [
            'data' => $logs,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page
        ];
    }
}

