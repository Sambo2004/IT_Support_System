<?php
// app/Controllers/UserController.php

class UserController {
    private User $userModel;
    private Department $deptModel;

    public function __construct() {
        AuthMiddleware::handle(['admin']);
        $this->userModel = new User();
        $this->deptModel = new Department();
    }

    public function index(): void {
        $users = $this->userModel->findAll();
        view('users.index', ['users' => $users]);
    }

    public function create(): void {
        $departments = $this->deptModel->getActive();
        view('users.create', ['departments' => $departments]);
    }

    public function store(): void {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = $_POST['role'] ?? 'employee';
        $departmentId = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;

        if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
            Flash::error('Please fill in all required fields.');
            $_SESSION['old_input'] = $_POST;
            redirect('/users/create');
        }

        if ($this->userModel->findByUsername($username)) {
            Flash::error('Username already taken.');
            $_SESSION['old_input'] = $_POST;
            redirect('/users/create');
        }

        if ($this->userModel->findByEmail($email)) {
            Flash::error('Email already registered.');
            $_SESSION['old_input'] = $_POST;
            redirect('/users/create');
        }

        $userId = $this->userModel->create([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'full_name' => $fullName,
            'phone' => $phone,
            'role' => $role,
            'department_id' => $departmentId,
            'is_active' => 1
        ]);

        AuditLog::record(Session::userId(), 'Create User', 'users', $userId, "Created user {$username}");
        Flash::success("User {$username} created successfully.");
        redirect('/users');
    }

    public function edit(string $id): void {
        $user = $this->userModel->findById((int)$id);
        if (!$user) {
            view('errors.404');
            return;
        }
        $departments = $this->deptModel->getActive();
        view('users.edit', ['user' => $user, 'departments' => $departments]);
    }

    public function update(string $id): void {
        $userId = (int)$id;
        $user = $this->userModel->findById($userId);
        if (!$user) {
            view('errors.404');
            return;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($fullName) || empty($email)) {
            Flash::error('Full name and email cannot be empty.');
            redirect("/users/{$userId}/edit");
        }

        if ($email !== $user['email']) {
            $existing = $this->userModel->findByEmail($email);
            if ($existing && (int)$existing['id'] !== $userId) {
                Flash::error('Email is already in use by another account.');
                redirect("/users/{$userId}/edit");
            }
        }

        $phone = trim($_POST['phone'] ?? '');
        $role = $_POST['role'] ?? 'employee';
        $departmentId = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
        $password = $_POST['password'] ?? '';

        $data = [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'role' => $role,
            'department_id' => $departmentId
        ];

        if (!empty($password)) {
            $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $this->userModel->update($userId, $data);
        AuditLog::record(Session::userId(), 'Update User', 'users', $userId, "Updated profile for {$user['username']}");

        Flash::success("User updated successfully.");
        redirect('/users');
    }

    public function toggleActive(string $id): void {
        $userId = (int)$id;
        if ($userId === Session::userId()) {
            Flash::error('You cannot deactivate your own account.');
            redirect('/users');
        }

        $this->userModel->toggleActive($userId);
        AuditLog::record(Session::userId(), 'Toggle User Active', 'users', $userId, "Toggled status");

        Flash::success("User status changed.");
        redirect('/users');
    }
}

