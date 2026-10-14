<?php
// app/Models/Notification.php

class Notification {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function create(int $userId, string $type, string $title, string $message, ?string $link = null): int {
        $stmt = $this->db->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $type, $title, $message, $link]);
        return (int)$this->db->lastInsertId();
    }

    public function createForRole(string $role, string $type, string $title, string $message, ?string $link = null): void {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = ? AND is_active = 1");
        $stmt->execute([$role]);
        $users = $stmt->fetchAll();
        foreach ($users as $user) {
            $this->create($user['id'], $type, $title, $message, $link);
        }
    }

    public function getByUserId(int $userId, int $limit = 50): array {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT " . (int)$limit);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function markAsRead(int $id, int $userId): bool {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }

    public function markAllAsRead(int $userId): bool {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    public function getUnreadCount(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}

