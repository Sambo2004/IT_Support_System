<?php
// app/Controllers/DepartmentController.php

class DepartmentController {
    private Department $deptModel;

    public function __construct() {
        AuthMiddleware::handle(['admin']);
        $this->deptModel = new Department();
    }

    public function index(): void {
        $departments = $this->deptModel->getWithUserCount();
        view('departments.index', ['departments' => $departments]);
    }

    public function create(): void {
        view('departments.form', ['department' => null]);
    }

    public function store(): void {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name)) {
            Flash::error('Department name is required.');
            redirect('/departments/create');
        }

        $id = $this->deptModel->create([
            'name' => $name,
            'description' => $description,
            'is_active' => $isActive
        ]);

        AuditLog::record(Session::userId(), 'Create Department', 'departments', $id, "Created {$name}");
        Flash::success("Department '{$name}' created.");
        redirect('/departments');
    }

    public function edit(string $id): void {
        $department = $this->deptModel->findById((int)$id);
        if (!$department) {
            view('errors.404');
            return;
        }
        view('departments.form', ['department' => $department]);
    }

    public function update(string $id): void {
        $deptId = (int)$id;
        $department = $this->deptModel->findById($deptId);
        if (!$department) {
            view('errors.404');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name)) {
            Flash::error('Department name is required.');
            redirect("/departments/{$deptId}/edit");
        }

        $this->deptModel->update($deptId, [
            'name' => $name,
            'description' => $description,
            'is_active' => $isActive
        ]);

        AuditLog::record(Session::userId(), 'Update Department', 'departments', $deptId, "Updated {$name}");
        Flash::success("Department updated.");
        redirect('/departments');
    }
}

