<?php
/**
 * Ticket Model
 * 
 * Handles all database operations related to support tickets including
 * creation, retrieval, status updates, assignment, and history tracking.
 */

require_once BASE_PATH . '/config/database.php';

class Ticket
{
    /**
     * @var PDO Database connection instance
     */
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Generate a unique ticket number in IT-YYYY-NNNN format.
     * 
     * Uses a dedicated sequence table with INSERT ... ON DUPLICATE KEY UPDATE
     * to safely increment the counter within a transaction.
     *
     * @return string The generated ticket number (e.g., IT-2026-0001)
     */
    public function generateTicketNumber(): string
    {
        $year = (int) date('Y');

        // Atomically increment the counter for this year
        $stmt = $this->db->prepare(
            "INSERT INTO ticket_number_seq (year, last_number) VALUES (?, 1)
             ON DUPLICATE KEY UPDATE last_number = last_number + 1"
        );
        $stmt->execute([$year]);

        // Retrieve the current counter value
        $stmt = $this->db->prepare("SELECT last_number FROM ticket_number_seq WHERE year = ?");
        $stmt->execute([$year]);
        $num = $stmt->fetchColumn();

        return sprintf("IT-%d-%04d", $year, $num);
    }

    /**
     * Create a new ticket.
     *
     * Wraps ticket number generation and insertion in a single transaction
     * to ensure the ticket number is never duplicated.
     *
     * @param array $data Ticket data with keys: title, description, category_id,
     *                    requester_id, department_id, priority, asset_id (optional)
     * @return int The ID of the newly created ticket
     */
    public function create(array $data): int
    {
        $this->db->beginTransaction();

        try {
            // Generate a unique ticket number within the transaction
            $ticketNumber = $this->generateTicketNumber();

            $stmt = $this->db->prepare(
                "INSERT INTO tickets (ticket_number, title, description, category_id,
                    requester_id, department_id, priority, status, asset_id, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'open', ?, NOW(), NOW())"
            );

            $stmt->execute([
                $ticketNumber,
                $data['title'],
                $data['description'],
                $data['category_id'],
                $data['requester_id'],
                $data['department_id'],
                $data['priority'],
                $data['asset_id'] ?? null
            ]);

            $id = (int) $this->db->lastInsertId();
            $this->db->commit();

            return $id;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Find a ticket by its ID, including related data via JOINs.
     *
     * @param int $id Ticket ID
     * @return array|false Ticket data or false if not found
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare(
            "SELECT t.*,
                    requester.full_name AS requester_name,
                    requester.email AS requester_email,
                    tech.full_name AS assigned_to_name,
                    tech.full_name AS assigned_name,
                    tc.name AS category_name,
                    d.name AS department_name,
                    a.asset_tag,
                    a.device_name AS asset_name
             FROM tickets t
             LEFT JOIN users requester ON t.requester_id = requester.id
             LEFT JOIN users tech ON t.assigned_to = tech.id
             LEFT JOIN ticket_categories tc ON t.category_id = tc.id
             LEFT JOIN departments d ON t.department_id = d.id
             LEFT JOIN assets a ON t.asset_id = a.id
             WHERE t.id = ?"
        );
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    /**
     * Retrieve a paginated, filtered list of tickets.
     *
     * Supported filters:
     *  - status: exact match on ticket status
     *  - priority: exact match on ticket priority
     *  - category_id: exact match on category
     *  - assigned_to: exact match on assigned technician
     *  - requester_id: exact match on ticket requester
     *  - search: LIKE match on ticket_number or title
     *  - date_from / date_to: range filter on created_at
     *
     * @param array $filters Key-value filter pairs
     * @param int   $page    Current page number (1-based)
     * @param int   $perPage Number of items per page
     * @return array ['data' => rows, 'total' => count, 'pages' => total_pages, 'current_page' => page]
     */
    public function findAll(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        // Build the WHERE clause dynamically
        $where = [];
        $params = [];
        $this->buildWhereClause($filters, $where, $params);

        $whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Count total matching rows
        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM tickets t {$whereSQL}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Calculate pagination
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        // Fetch the page of results with JOINed data
        $stmt = $this->db->prepare(
            "SELECT t.*,
                    requester.full_name AS requester_name,
                    tech.full_name AS assigned_to_name,
                    tech.full_name AS assigned_name,
                    tc.name AS category_name,
                    d.name AS department_name,
                    a.asset_tag
             FROM tickets t
             LEFT JOIN users requester ON t.requester_id = requester.id
             LEFT JOIN users tech ON t.assigned_to = tech.id
             LEFT JOIN ticket_categories tc ON t.category_id = tc.id
             LEFT JOIN departments d ON t.department_id = d.id
             LEFT JOIN assets a ON t.asset_id = a.id
             {$whereSQL}
             ORDER BY t.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute($params);

        return [
            'data'         => $stmt->fetchAll(),
            'total'        => $total,
            'pages'        => $totalPages,
            'current_page' => $page,
        ];
    }

    /**
     * Update a ticket's fields.
     *
     * @param int   $id   Ticket ID
     * @param array $data Associative array of column => value to update
     * @return bool True on success
     */
    public function update(int $id, array $data): bool
    {
        // Only allow updating known columns
        $allowed = [
            'title', 'description', 'category_id', 'department_id',
            'priority', 'status', 'assigned_to', 'asset_id', 'resolution', 'resolved_at'
        ];

        $sets = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $sets[] = "{$key} = ?";
                $params[] = $value;
            }
        }

        if (empty($sets)) {
            return false;
        }

        // Always touch updated_at
        $sets[] = "updated_at = NOW()";
        $params[] = $id;

        $stmt = $this->db->prepare(
            "UPDATE tickets SET " . implode(', ', $sets) . " WHERE id = ?"
        );

        return $stmt->execute($params);
    }

    /**
     * Update ticket status with history logging.
     *
     * If the new status is 'resolved', also records the resolution timestamp
     * and optional resolution text.
     *
     * @param int         $id         Ticket ID
     * @param string      $status     New status value
     * @param int         $userId     ID of user performing the change
     * @param string|null $resolution Optional resolution text (for 'resolved' status)
     * @return bool
     */
    public function updateStatus(int $id, string $status, int $userId, ?string $resolution = null): bool
    {
        // Get current status for history logging
        $stmt = $this->db->prepare("SELECT status FROM tickets WHERE id = ?");
        $stmt->execute([$id]);
        $oldStatus = $stmt->fetchColumn();

        if ($oldStatus === false) {
            return false;
        }

        // Prepare update data
        $updateData = ['status' => $status];

        if ($status === 'resolved') {
            $updateData['resolved_at'] = date('Y-m-d H:i:s');
            if ($resolution !== null) {
                $updateData['resolution'] = $resolution;
            }
        }

        // If reopening, clear resolved_at
        if ($status === 'open' && in_array($oldStatus, ['resolved', 'closed'], true)) {
            $updateData['resolved_at'] = null;
        }

        $this->update($id, $updateData);

        // Log the status change in ticket history
        $this->logHistory($id, $userId, 'status', $oldStatus, $status);

        return true;
    }

    /**
     * Assign a technician to a ticket.
     *
     * If the ticket is currently 'open', its status changes to 'assigned'.
     *
     * @param int $id     Ticket ID
     * @param int $techId Technician user ID
     * @param int $userId ID of user performing the assignment
     * @return bool
     */
    public function assignTechnician(int $id, int $techId, int $userId): bool
    {
        // Get current assignment and status
        $stmt = $this->db->prepare("SELECT assigned_to, status FROM tickets WHERE id = ?");
        $stmt->execute([$id]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            return false;
        }

        $oldAssigned = $ticket['assigned_to'];

        // Build update data
        $updateData = ['assigned_to' => $techId];

        // Auto-set status to 'assigned' if the ticket is still open
        if ($ticket['status'] === 'open') {
            $updateData['status'] = 'assigned';
            $this->logHistory($id, $userId, 'status', 'open', 'assigned');
        }

        $this->update($id, $updateData);

        // Log the assignment change
        $this->logHistory($id, $userId, 'assigned_to', $oldAssigned, $techId);

        return true;
    }

    /**
     * Get the number of tickets grouped by status.
     *
     * @return array Associative array of status => count
     */
    public function getStatusCounts(): array
    {
        $stmt = $this->db->query("SELECT status, COUNT(*) as count FROM tickets GROUP BY status");
        $rows = $stmt->fetchAll();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['count'];
        }

        return $counts;
    }

    /**
     * Get the number of tickets grouped by priority.
     *
     * @return array Associative array of priority => count
     */
    public function getPriorityCounts(): array
    {
        $stmt = $this->db->query("SELECT priority, COUNT(*) as count FROM tickets GROUP BY priority");
        $rows = $stmt->fetchAll();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['priority']] = (int) $row['count'];
        }

        return $counts;
    }

    /**
     * Get the most recent tickets, optionally filtered by user and role.
     *
     * @param int         $limit  Number of tickets to return
     * @param int|null    $userId User ID for role-based filtering
     * @param string|null $role   User role ('technician' or 'employee')
     * @return array
     */
    public function getRecentTickets(int $limit = 5, ?int $userId = null, ?string $role = null): array
    {
        $where = [];
        $params = [];

        // Technicians see only tickets assigned to them
        if ($role === 'technician' && $userId !== null) {
            $where[] = "t.assigned_to = ?";
            $params[] = $userId;
        }

        // Employees see only their own tickets
        if ($role === 'employee' && $userId !== null) {
            $where[] = "t.requester_id = ?";
            $params[] = $userId;
        }

        $whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare(
            "SELECT t.*,
                    requester.full_name AS requester_name,
                    tech.full_name AS assigned_to_name,
                    tech.full_name AS assigned_name,
                    tc.name AS category_name
             FROM tickets t
             LEFT JOIN users requester ON t.requester_id = requester.id
             LEFT JOIN users tech ON t.assigned_to = tech.id
             LEFT JOIN ticket_categories tc ON t.category_id = tc.id
             {$whereSQL}
             ORDER BY t.created_at DESC
             LIMIT {$limit}"
        );
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Log a change to ticket history.
     *
     * @param int         $ticketId Ticket ID
     * @param int         $userId   User who made the change
     * @param string      $field    Name of the field that changed
     * @param string|null $oldValue Previous value
     * @param string|null $newValue New value
     * @return void
     */
    public function logHistory(int $ticketId, int $userId, string $field, ?string $oldValue, ?string $newValue): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO ticket_history (ticket_id, user_id, field_changed, old_value, new_value, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$ticketId, $userId, $field, $oldValue, $newValue]);
    }

    /**
     * Get the full history for a ticket, ordered newest first.
     *
     * @param int $ticketId Ticket ID
     * @return array List of history entries with user names
     */
    public function getHistory(int $ticketId): array
    {
        $stmt = $this->db->prepare(
            "SELECT th.*, u.full_name AS user_name, u.full_name AS changed_by_name
             FROM ticket_history th
             LEFT JOIN users u ON th.user_id = u.id
             WHERE th.ticket_id = ?
             ORDER BY th.created_at DESC"
        );
        $stmt->execute([$ticketId]);

        return $stmt->fetchAll();
    }

    /**
     * Count total tickets matching the given filters (same logic as findAll).
     *
     * @param array $filters Key-value filter pairs
     * @return int Total number of matching tickets
     */
    public function countAll(array $filters = []): int
    {
        $where = [];
        $params = [];
        $this->buildWhereClause($filters, $where, $params);

        $whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tickets t {$whereSQL}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Build WHERE clause fragments and parameter array from filters.
     *
     * This helper is shared between findAll() and countAll() to keep
     * the filtering logic consistent.
     *
     * @param array $filters Input filters
     * @param array &$where  Output WHERE clause fragments (modified by reference)
     * @param array &$params Output parameter values (modified by reference)
     */
    private function buildWhereClause(array $filters, array &$where, array &$params): void
    {
        if (!empty($filters['status'])) {
            $where[] = "t.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $where[] = "t.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['category_id'])) {
            $where[] = "t.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['assigned_to'])) {
            $where[] = "t.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }

        if (!empty($filters['requester_id'])) {
            $where[] = "t.requester_id = ?";
            $params[] = $filters['requester_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(t.ticket_number LIKE ? OR t.title LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['date_from'])) {
            $where[] = "t.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = "t.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
    }
}
