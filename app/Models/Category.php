<?php
// app/Models/Category.php

class Category {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findAll(): array {
        $stmt = $this->db->query("SELECT * FROM ticket_categories ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function getActive(): array {
        $stmt = $this->db->query("SELECT * FROM ticket_categories WHERE is_active = 1 ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function findById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM ticket_categories WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getWithTicketCount(): array {
        $sql = "SELECT c.*, COUNT(t.id) as ticket_count 
                FROM ticket_categories c 
                LEFT JOIN tickets t ON c.id = t.category_id 
                GROUP BY c.id 
                ORDER BY c.name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("INSERT INTO ticket_categories (name, description, is_active) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            isset($data['is_active']) ? (int)$data['is_active'] : 1
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE ticket_categories SET name = ?, description = ?, is_active = ? WHERE id = ?");
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
            $id
        ]);
    }
}

