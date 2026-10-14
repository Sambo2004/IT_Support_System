<?php
// app/Models/Attachment.php

class Attachment {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO ticket_attachments (ticket_id, user_id, file_name, stored_name, file_size, mime_type, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['ticket_id'],
            $data['user_id'],
            $data['file_name'],
            $data['stored_name'],
            $data['file_size'],
            $data['mime_type']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getByTicketId(int $ticketId): array {
        $stmt = $this->db->prepare("
            SELECT a.*, u.full_name AS uploader_name, u.role AS uploader_role
            FROM ticket_attachments a
            JOIN users u ON a.user_id = u.id
            WHERE a.ticket_id = ?
            ORDER BY a.created_at ASC
        ");
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT a.*, t.ticket_number, t.requester_id, t.assigned_to, u.full_name AS uploader_name
            FROM ticket_attachments a
            JOIN tickets t ON a.ticket_id = t.id
            JOIN users u ON a.user_id = u.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM ticket_attachments WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

