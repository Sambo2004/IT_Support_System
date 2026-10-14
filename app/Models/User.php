<?php
// app/Models/User.php

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findByUsername(string $username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findByEmail(string $email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById(int $id) {
        $sql = "SELECT u.*, d.name AS department_name 
                FROM users u 
                LEFT JOIN departments d ON u.department_id = d.id 
                WHERE u.id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findAll(array $filters = []): array {
        $sql = "SELECT u.*, d.name AS department_name 
                FROM users u 
                LEFT JOIN departments d ON u.department_id = d.id 
                WHERE 1=1";
        $params = [];

        if (!empty($filters['role'])) {
            $sql .= " AND u.role = ?";
            $params[] = $filters['role'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND u.is_active = ?";
            $params[] = (int)$filters['is_active'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $sql .= " AND (u.username LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY u.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $sql = "INSERT INTO users (username, email, password_hash, full_name, phone, role, department_id, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['username'],
            $data['email'],
            $data['password_hash'],
            $data['full_name'],
            $data['phone'] ?? null,
            $data['role'] ?? 'employee',
            $data['department_id'] ?: null,
            isset($data['is_active']) ? (int)$data['is_active'] : 1
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [];

        if (isset($data['full_name'])) {
            $fields[] = "full_name = ?";
            $params[] = $data['full_name'];
        }
        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $params[] = $data['email'];
        }
        if (isset($data['phone'])) {
            $fields[] = "phone = ?";
            $params[] = $data['phone'];
        }
        if (isset($data['role'])) {
            $fields[] = "role = ?";
            $params[] = $data['role'];
        }
        if (array_key_exists('department_id', $data)) {
            $fields[] = "department_id = ?";
            $params[] = $data['department_id'] ?: null;
        }
        if (isset($data['password_hash'])) {
            $fields[] = "password_hash = ?";
            $params[] = $data['password_hash'];
        }
        if (isset($data['is_active'])) {
            $fields[] = "is_active = ?";
            $params[] = (int)$data['is_active'];
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function toggleActive(int $id): bool {
        $stmt = $this->db->prepare("UPDATE users SET is_active = IF(is_active=1, 0, 1) WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateLastLogin(int $id): void {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW(), login_attempts = 0, locked_until = NULL WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function incrementLoginAttempts(int $id): void {
        $stmt = $this->db->prepare("SELECT login_attempts FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $attempts = (int)$stmt->fetchColumn() + 1;

        if ($attempts >= 5) {
            $lockedUntil = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $update = $this->db->prepare("UPDATE users SET login_attempts = ?, locked_until = ? WHERE id = ?");
            $update->execute([$attempts, $lockedUntil, $id]);
        } else {
            $update = $this->db->prepare("UPDATE users SET login_attempts = ? WHERE id = ?");
            $update->execute([$attempts, $id]);
        }
    }

    public function resetLoginAttempts(int $id): void {
        $stmt = $this->db->prepare("UPDATE users SET login_attempts = 0, locked_until = NULL WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function getTechnicians(): array {
        $stmt = $this->db->prepare("SELECT id, username, full_name, email FROM users WHERE role = 'technician' AND is_active = 1 ORDER BY full_name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countByRole(): array {
        $stmt = $this->db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
        $counts = [];
        while ($row = $stmt->fetch()) {
            $counts[$row['role']] = (int)$row['count'];
        }
        return $counts;
    }
}

