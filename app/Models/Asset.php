<?php
// app/Models/Asset.php

class Asset {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findAll(array $filters = []): array {
        $sql = "SELECT a.*, ac.name as category_name, u.full_name as assigned_to_name, d.name as department_name 
                FROM assets a 
                LEFT JOIN asset_categories ac ON a.category_id = ac.id 
                LEFT JOIN users u ON a.assigned_to = u.id 
                LEFT JOIN departments d ON a.department_id = d.id 
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['category_id'])) {
            $sql .= " AND a.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $sql .= " AND (a.asset_tag LIKE ? OR a.device_name LIKE ? OR a.serial_number LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY a.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id) {
        $sql = "SELECT a.*, ac.name as category_name, u.full_name as assigned_to_name, d.name as department_name 
                FROM assets a 
                LEFT JOIN asset_categories ac ON a.category_id = ac.id 
                LEFT JOIN users u ON a.assigned_to = u.id 
                LEFT JOIN departments d ON a.department_id = d.id 
                WHERE a.id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getCategories(): array {
        $stmt = $this->db->query("SELECT * FROM asset_categories ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function getExpiringWarranties(int $days = 60): array {
        $stmt = $this->db->prepare(
            "SELECT a.*, ac.name as category_name, u.full_name as assigned_to_name,
                    DATEDIFF(a.warranty_expiry, CURDATE()) as days_remaining
             FROM assets a
             LEFT JOIN asset_categories ac ON a.category_id = ac.id
             LEFT JOIN users u ON a.assigned_to = u.id
             WHERE a.warranty_expiry IS NOT NULL 
               AND a.warranty_expiry >= CURDATE()
               AND a.warranty_expiry <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
             ORDER BY a.warranty_expiry ASC"
        );
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $sql = "INSERT INTO assets (asset_tag, device_name, category_id, brand, model, serial_number, purchase_date, warranty_expiry, assigned_to, department_id, location, status, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['asset_tag'],
            $data['device_name'],
            $data['category_id'],
            $data['brand'] ?? null,
            $data['model'] ?? null,
            $data['serial_number'] ?? null,
            $data['purchase_date'] ?? null,
            $data['warranty_expiry'] ?? null,
            $data['assigned_to'] ?? null,
            $data['department_id'] ?? null,
            $data['location'] ?? null,
            $data['status'] ?? 'available',
            $data['notes'] ?? null
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE assets SET 
                asset_tag = ?, device_name = ?, category_id = ?, brand = ?, model = ?, serial_number = ?, 
                purchase_date = ?, warranty_expiry = ?, assigned_to = ?, department_id = ?, location = ?, 
                status = ?, notes = ? 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['asset_tag'],
            $data['device_name'],
            $data['category_id'],
            $data['brand'] ?? null,
            $data['model'] ?? null,
            $data['serial_number'] ?? null,
            $data['purchase_date'] ?: null,
            $data['warranty_expiry'] ?: null,
            $data['assigned_to'] ?: null,
            $data['department_id'] ?: null,
            $data['location'] ?? null,
            $data['status'] ?? 'available',
            $data['notes'] ?? null,
            $id
        ]);
    }

    public function getStatusCounts(): array {
        $stmt = $this->db->query("SELECT status, COUNT(*) as count FROM assets GROUP BY status");
        $res = ['available' => 0, 'assigned' => 0, 'under_maintenance' => 0, 'retired' => 0];
        while ($row = $stmt->fetch()) {
            $res[$row['status']] = (int)$row['count'];
        }
        return $res;
    }

    public function delete(int $id): bool {
        // Prevent deletion if asset is referenced by existing tickets
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tickets WHERE asset_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM assets WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

