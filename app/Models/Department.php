<?php
// app/Models/Department.php

class Department {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findAll(): array {
        $stmt = $this->db->query("SELECT * FROM departments ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function getActive(): array {
        $stmt = $this->db->query("SELECT * FROM departments WHERE is_active = 1 ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function findById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM departments WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getWithUserCount(): array {
        $sql = "SELECT d.*, COUNT(u.id) as user_count 
                FROM departments d 
                LEFT JOIN users u ON d.id = u.department_id 
                GROUP BY d.id 
                ORDER BY d.name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("INSERT INTO departments (name, description, is_active) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            isset($data['is_active']) ? (int)$data['is_active'] : 1
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE departments SET name = ?, description = ?, is_active = ? WHERE id = ?");
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
            $id
        ]);
    }
}

