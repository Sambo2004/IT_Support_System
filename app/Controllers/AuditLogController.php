<?php
// app/Controllers/AuditLogController.php

class AuditLogController {
    private AuditLog $auditModel;

    public function __construct() {
        AuthMiddleware::handle(['admin']);
        $this->auditModel = new AuditLog();
    }

    public function index(): void {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $logsData = $this->auditModel->getAll($page, 20);

        view('audit_logs.index', [
            'logs' => $logsData['data'],
            'total' => $logsData['total'],
            'totalPages' => $logsData['pages'],
            'currentPage' => $logsData['current_page']
        ]);
    }
}

