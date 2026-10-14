<?php
// app/Models/TicketComment.php

class TicketComment {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function create(int $ticketId, int $userId, string $comment, bool $isInternal = false): int {
        $stmt = $this->db->prepare("INSERT INTO ticket_comments (ticket_id, user_id, comment, is_internal) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ticketId, $userId, $comment, $isInternal ? 1 : 0]);
        return (int)$this->db->lastInsertId();
    }

    public function getByTicketId(int $ticketId, bool $showInternal = false): array {
        $sql = "SELECT tc.*, u.full_name as author_name, u.role as author_role 
                FROM ticket_comments tc 
                JOIN users u ON tc.user_id = u.id 
                WHERE tc.ticket_id = ?";
        if (!$showInternal) {
            $sql .= " AND tc.is_internal = 0";
        }
        $sql .= " ORDER BY tc.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    }
}

