<?php
// app/Controllers/TicketController.php

class TicketController {
    private Ticket $ticketModel;
    private Category $categoryModel;
    private Department $departmentModel;
    private User $userModel;
    private Asset $assetModel;
    private Notification $notificationModel;
    private Attachment $attachmentModel;

    public function __construct() {
        AuthMiddleware::handle();
        $this->ticketModel = new Ticket();
        $this->categoryModel = new Category();
        $this->departmentModel = new Department();
        $this->userModel = new User();
        $this->assetModel = new Asset();
        $this->notificationModel = new Notification();
        $this->attachmentModel = new Attachment();
    }

    public function index(): void {
        $user = Session::user();
        $filters = [
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        // Role-based visibility
        if ($user['role'] === 'employee') {
            $filters['requester_id'] = $user['id'];
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $ticketsData = $this->ticketModel->findAll($filters, $page, 15);
        $categories = $this->categoryModel->getActive();
        $technicians = $this->userModel->getTechnicians();

        view('tickets.index', [
            'tickets' => $ticketsData['data'],
            'total' => $ticketsData['total'],
            'totalPages' => $ticketsData['pages'],
            'currentPage' => $ticketsData['current_page'],
            'categories' => $categories,
            'technicians' => $technicians,
            'filters' => $filters
        ]);
    }

    public function create(): void {
        $categories = $this->categoryModel->getActive();
        $departments = $this->departmentModel->getActive();
        $assets = $this->assetModel->findAll(['status' => 'assigned']);

        view('tickets.create', [
            'categories' => $categories,
            'departments' => $departments,
            'assets' => $assets
        ]);
    }

    public function store(): void {
        $user = Session::user();
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $priority = $_POST['priority'] ?? 'medium';
        $assetId = !empty($_POST['asset_id']) ? (int)$_POST['asset_id'] : null;

        if (empty($title) || empty($description) || !$categoryId) {
            Flash::error('Please fill in all required fields.');
            $_SESSION['old_input'] = $_POST;
            redirect('/tickets/create');
        }

        $ticketId = $this->ticketModel->create([
            'title' => $title,
            'description' => $description,
            'category_id' => $categoryId,
            'requester_id' => $user['id'],
            'department_id' => $user['department_id'],
            'priority' => $priority,
            'asset_id' => $assetId
        ]);

        $ticket = $this->ticketModel->findById($ticketId);

        // Process optional initial attachment
        $this->handleFileUpload($ticketId, $user['id']);

        // Notify Admins & Technicians
        $this->notificationModel->createForRole('admin', 'ticket_created', 'New Ticket Created', "Ticket #{$ticket['ticket_number']} submitted by {$user['full_name']}", "/tickets/{$ticketId}");
        $this->notificationModel->createForRole('technician', 'ticket_created', 'New Ticket Available', "Ticket #{$ticket['ticket_number']}: {$ticket['title']}", "/tickets/{$ticketId}");

        AuditLog::record($user['id'], 'Ticket Created', 'tickets', $ticketId, "Created ticket {$ticket['ticket_number']}");

        Flash::success("Ticket #{$ticket['ticket_number']} has been created successfully.");
        redirect("/tickets/{$ticketId}");
    }

    public function show(string $id): void {
        $ticketId = (int)$id;
        $ticket = $this->ticketModel->findById($ticketId);

        if (!$ticket) {
            view('errors.404');
            return;
        }

        $user = Session::user();
        // Permission check
        if ($user['role'] === 'employee' && $ticket['requester_id'] != $user['id']) {
            view('errors.403');
            return;
        }

        $commentModel = new TicketComment();
        $showInternal = in_array($user['role'], ['admin', 'technician', 'manager']);
        $comments = $commentModel->getByTicketId($ticketId, $showInternal);
        $history = $this->ticketModel->getHistory($ticketId);
        $technicians = $this->userModel->getTechnicians();
        $attachments = $this->attachmentModel->getByTicketId($ticketId);

        view('tickets.show', [
            'ticket' => $ticket,
            'comments' => $comments,
            'history' => $history,
            'technicians' => $technicians,
            'attachments' => $attachments
        ]);
    }

    public function edit(string $id): void {
        $user = Session::user();
        $ticket = $this->ticketModel->findById((int)$id);
        if (!$ticket) {
            view('errors.404');
            return;
        }

        // Only Admin or the specific Assigned Technician can edit ticket details
        $isAdmin = ($user['role'] === 'admin');
        $isAssignedTech = ($user['role'] === 'technician' && !empty($ticket['assigned_to']) && (int)$ticket['assigned_to'] === (int)$user['id']);

        if (!$isAdmin && !$isAssignedTech) {
            view('errors.403');
            return;
        }

        $categories = $this->categoryModel->getActive();
        $technicians = $this->userModel->getTechnicians();

        view('tickets.edit', [
            'ticket' => $ticket,
            'categories' => $categories,
            'technicians' => $technicians
        ]);
    }

    public function update(string $id): void {
        $ticketId = (int)$id;
        $user = Session::user();
        $ticket = $this->ticketModel->findById($ticketId);
        if (!$ticket) {
            view('errors.404');
            return;
        }

        // Only Admin or the specific Assigned Technician can update ticket details
        $isAdmin = ($user['role'] === 'admin');
        $isAssignedTech = ($user['role'] === 'technician' && !empty($ticket['assigned_to']) && (int)$ticket['assigned_to'] === (int)$user['id']);

        if (!$isAdmin && !$isAssignedTech) {
            view('errors.403');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $priority = $_POST['priority'] ?? 'medium';

        $this->ticketModel->update($ticketId, [
            'title' => $title,
            'description' => $description,
            'category_id' => $categoryId,
            'priority' => $priority
        ]);

        AuditLog::record($user['id'], 'Ticket Updated', 'tickets', $ticketId, "Updated ticket details");
        Flash::success("Ticket updated successfully.");
        redirect("/tickets/{$ticketId}");
    }

    public function assign(string $id): void {
        $ticketId = (int)$id;
        $user = Session::user();
        if ($user['role'] !== 'admin') {
            view('errors.403');
            return;
        }

        $techId = (int)($_POST['assigned_to'] ?? 0);
        if ($techId > 0) {
            $this->ticketModel->assignTechnician($ticketId, $techId, $user['id']);
            $ticket = $this->ticketModel->findById($ticketId);

            $this->notificationModel->create($techId, 'ticket_assigned', 'Ticket Assigned to You', "You have been assigned to Ticket #{$ticket['ticket_number']}", "/tickets/{$ticketId}");
            AuditLog::record($user['id'], 'Ticket Assigned', 'tickets', $ticketId, "Assigned to tech ID {$techId}");

            Flash::success("Technician assigned successfully.");
        }

        redirect("/tickets/{$ticketId}");
    }

    public function updateStatus(string $id): void {
        $ticketId = (int)$id;
        $user = Session::user();
        $ticket = $this->ticketModel->findById($ticketId);

        if (!$ticket) {
            view('errors.404');
            return;
        }

        // Server-side authorization check:
        // Admin can update any ticket.
        // Technician can only update tickets assigned to them.
        // Employee can only close their own resolved ticket, or reopen their own closed/resolved ticket.
        $isAdmin = ($user['role'] === 'admin');
        $isAssignedTech = ($user['role'] === 'technician' && !empty($ticket['assigned_to']) && (int)$ticket['assigned_to'] === (int)$user['id']);
        $isRequester = ((int)$ticket['requester_id'] === (int)$user['id']);

        if (!$isAdmin && !$isAssignedTech && !$isRequester) {
            view('errors.403');
            return;
        }

        $newStatus = $_POST['status'] ?? '';
        $resolution = trim($_POST['resolution'] ?? '');
        $currentStatus = $ticket['status'];

        // Strict allowed state transitions:
        // open        -> assigned, in_progress, closed
        // assigned    -> in_progress, pending, closed
        // in_progress -> pending, resolved, closed
        // pending     -> in_progress, resolved, closed
        // resolved    -> closed, open (reopen)
        // closed      -> open (reopen - admin only)
        $allowedTransitions = [
            'open'        => ['assigned', 'in_progress', 'closed'],
            'assigned'    => ['in_progress', 'pending', 'closed'],
            'in_progress' => ['pending', 'resolved', 'closed'],
            'pending'     => ['in_progress', 'resolved', 'closed'],
            'resolved'    => ['closed', 'open'],
            'closed'      => ['open']
        ];

        // Employees can only transition their own tickets to 'closed' (accept resolution) or 'open' (reopen)
        if (!$isAdmin && !$isAssignedTech && $isRequester) {
            if (!in_array($newStatus, ['closed', 'open'])) {
                Flash::error('Employees may only close or reopen resolved tickets.');
                redirect("/tickets/{$ticketId}");
            }
        }

        // Reopening closed tickets is restricted to admin
        if ($currentStatus === 'closed' && $newStatus === 'open' && !$isAdmin) {
            Flash::error('Only administrators can reopen a closed ticket.');
            redirect("/tickets/{$ticketId}");
        }

        if (!isset($allowedTransitions[$currentStatus]) || !in_array($newStatus, $allowedTransitions[$currentStatus])) {
            Flash::error("Invalid status transition from " . ucfirst(str_replace('_', ' ', $currentStatus)) . " to " . ucfirst(str_replace('_', ' ', $newStatus)) . ".");
            redirect("/tickets/{$ticketId}");
        }

        if ($newStatus === 'resolved' && empty($resolution)) {
            Flash::error('Resolution description is required when marking a ticket as resolved.');
            redirect("/tickets/{$ticketId}");
        }

        $this->ticketModel->updateStatus($ticketId, $newStatus, $user['id'], $resolution ?: null);

        // Notify Requester
        if ($ticket['requester_id'] != $user['id']) {
            $this->notificationModel->create(
                $ticket['requester_id'],
                'status_changed',
                "Ticket #{$ticket['ticket_number']} Status Updated",
                "Your ticket status changed to " . ucfirst(str_replace('_', ' ', $newStatus)),
                "/tickets/{$ticketId}"
            );
        }

        AuditLog::record($user['id'], 'Ticket Status Change', 'tickets', $ticketId, "Status updated to {$newStatus}");
        Flash::success("Ticket status updated to " . ucfirst(str_replace('_', ' ', $newStatus)));
        redirect("/tickets/{$ticketId}");
    }

    public function addComment(string $id): void {
        $ticketId = (int)$id;
        $user = Session::user();
        $ticket = $this->ticketModel->findById($ticketId);

        if (!$ticket) {
            view('errors.404');
            return;
        }

        // Authorization check: Admin, Manager, Assigned Tech, or the Requester
        $isAdmin = ($user['role'] === 'admin');
        $isManager = ($user['role'] === 'manager');
        $isAssignedTech = ($user['role'] === 'technician' && !empty($ticket['assigned_to']) && (int)$ticket['assigned_to'] === (int)$user['id']);
        $isRequester = ((int)$ticket['requester_id'] === (int)$user['id']);

        if (!$isAdmin && !$isManager && !$isAssignedTech && !$isRequester) {
            view('errors.403');
            return;
        }

        $comment = trim($_POST['comment'] ?? '');
        $isInternal = isset($_POST['is_internal']) && in_array($user['role'], ['admin', 'technician']);

        if (empty($comment)) {
            Flash::error('Comment cannot be empty.');
            redirect("/tickets/{$ticketId}");
        }

        $commentModel = new TicketComment();
        $commentModel->create($ticketId, $user['id'], $comment, $isInternal);

        $ticket = $this->ticketModel->findById($ticketId);
        // Notify technician if comment by user
        if ($user['id'] == $ticket['requester_id'] && !empty($ticket['assigned_to'])) {
            $this->notificationModel->create($ticket['assigned_to'], 'new_comment', "New comment on #{$ticket['ticket_number']}", "{$user['full_name']} commented on the ticket", "/tickets/{$ticketId}");
        } elseif ($user['id'] != $ticket['requester_id'] && !$isInternal) {
            // Notify user if public reply
            $this->notificationModel->create($ticket['requester_id'], 'new_comment', "New reply on #{$ticket['ticket_number']}", "New update on your support ticket", "/tickets/{$ticketId}");
        }

        Flash::success("Comment added.");
        redirect("/tickets/{$ticketId}");
    }

    public function uploadAttachment(string $id): void {
        $ticketId = (int)$id;
        $user = Session::user();
        $ticket = $this->ticketModel->findById($ticketId);

        if (!$ticket) {
            view('errors.404');
            return;
        }

        $isAdmin = ($user['role'] === 'admin');
        $isManager = ($user['role'] === 'manager');
        $isAssignedTech = ($user['role'] === 'technician' && !empty($ticket['assigned_to']) && (int)$ticket['assigned_to'] === (int)$user['id']);
        $isRequester = ((int)$ticket['requester_id'] === (int)$user['id']);

        if (!$isAdmin && !$isManager && !$isAssignedTech && !$isRequester) {
            view('errors.403');
            return;
        }

        $attachId = $this->handleFileUpload($ticketId, $user['id']);
        if ($attachId) {
            AuditLog::record($user['id'], 'File Attached', 'tickets', $ticketId, "Attached file to ticket #{$ticket['ticket_number']}");
            Flash::success('Attachment uploaded successfully.');
        }

        redirect("/tickets/{$ticketId}");
    }

    public function downloadAttachment(string $id): void {
        $attachmentId = (int)$id;
        $user = Session::user();
        $attachment = $this->attachmentModel->findById($attachmentId);

        if (!$attachment) {
            view('errors.404');
            return;
        }

        $isAdmin = ($user['role'] === 'admin');
        $isManager = ($user['role'] === 'manager');
        $isAssignedTech = ($user['role'] === 'technician' && !empty($attachment['assigned_to']) && (int)$attachment['assigned_to'] === (int)$user['id']);
        $isRequester = ((int)$attachment['requester_id'] === (int)$user['id']);

        if (!$isAdmin && !$isManager && !$isAssignedTech && !$isRequester) {
            view('errors.403');
            return;
        }

        $filePath = BASE_PATH . '/storage/attachments/' . $attachment['stored_name'];
        if (!file_exists($filePath)) {
            Flash::error('Attachment file not found on disk.');
            redirect("/tickets/{$attachment['ticket_id']}");
            return;
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . ($attachment['mime_type'] ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . rawurlencode($attachment['file_name']) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public function deleteAttachment(string $id): void {
        $attachmentId = (int)$id;
        $user = Session::user();
        $attachment = $this->attachmentModel->findById($attachmentId);

        if (!$attachment) {
            view('errors.404');
            return;
        }

        $isAdmin = ($user['role'] === 'admin');
        $isOwner = ((int)$attachment['user_id'] === (int)$user['id']);

        if (!$isAdmin && !$isOwner) {
            view('errors.403');
            return;
        }

        $filePath = BASE_PATH . '/storage/attachments/' . $attachment['stored_name'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $this->attachmentModel->delete($attachmentId);
        AuditLog::record($user['id'], 'File Attachment Deleted', 'tickets', $attachment['ticket_id'], "Deleted file '{$attachment['file_name']}'");

        Flash::success('Attachment removed.');
        redirect("/tickets/{$attachment['ticket_id']}");
    }

    private function handleFileUpload(int $ticketId, int $userId): ?int {
        if (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES['attachment'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Flash::error('File upload failed with error code: ' . $file['error']);
            return null;
        }

        $maxBytes = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxBytes) {
            Flash::error('Attachment must be 10MB or smaller.');
            return null;
        }

        $origName = basename($file['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar', '7z', 'csv'];
        if (!in_array($ext, $allowedExts)) {
            Flash::error("File type .{$ext} is not allowed. Supported formats: images, documents, spreadsheets, zip archives.");
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']) ?: 'application/octet-stream';
        finfo_close($finfo);

        $disallowedMimes = ['application/x-php', 'text/x-php', 'application/x-httpd-php', 'application/x-executable', 'text/x-shellscript'];
        if (in_array(strtolower($mime), $disallowedMimes)) {
            Flash::error('Executable file uploads are strictly prohibited.');
            return null;
        }

        $uploadDir = BASE_PATH . '/storage/attachments';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $randomName = bin2hex(random_bytes(16)) . '.' . $ext;
        $destPath = $uploadDir . '/' . $randomName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            Flash::error('Failed to store uploaded file.');
            return null;
        }

        return $this->attachmentModel->create([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'file_name' => $origName,
            'stored_name' => $randomName,
            'file_size' => (int)$file['size'],
            'mime_type' => $mime
        ]);
    }
}


