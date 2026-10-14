<?php
// app/Controllers/AuthController.php

class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function showLogin(): void {
        if (Session::isLoggedIn()) {
            redirect('/');
        }
        view('auth.login');
    }

    public function login(): void {
        if (!RateLimitMiddleware::check('login', 5, 15)) {
            Flash::error('Too many failed login attempts. Please wait 15 minutes.');
            redirect('/login');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            Flash::error('Please enter both username and password.');
            $_SESSION['old_input'] = ['username' => $username];
            redirect('/login');
        }

        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            RateLimitMiddleware::increment('login');
            Flash::error('Invalid credentials.');
            $_SESSION['old_input'] = ['username' => $username];
            redirect('/login');
        }

        // Check if locked
        if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
            $remaining = ceil((strtotime($user['locked_until']) - time()) / 60);
            Flash::error("This account is temporarily locked. Try again in {$remaining} minutes.");
            redirect('/login');
        }

        // Check active status
        if (!$user['is_active']) {
            Flash::error('This account is disabled. Contact your administrator.');
            redirect('/login');
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            RateLimitMiddleware::increment('login');
            $this->userModel->incrementLoginAttempts($user['id']);
            AuditLog::record($user['id'], 'Failed Login', 'users', $user['id'], 'Incorrect password');
            Flash::error('Invalid credentials.');
            $_SESSION['old_input'] = ['username' => $username];
            redirect('/login');
        }

        // Login success
        RateLimitMiddleware::reset('login');
        $this->userModel->resetLoginAttempts($user['id']);
        $this->userModel->updateLastLogin($user['id']);

        Session::setUser($user);
        AuditLog::record($user['id'], 'User Login', 'users', $user['id'], 'Login successful');

        Flash::success("Welcome back, {$user['full_name']}!");

        $intended = Session::get('intended_url', '/');
        Session::remove('intended_url');
        redirect($intended);
    }

    public function logout(): void {
        $userId = Session::userId();
        if ($userId) {
            AuditLog::record($userId, 'User Logout', 'users', $userId, 'Logout successful');
        }
        Session::destroy();
        redirect('/login');
    }

    public function showChangePassword(): void {
        AuthMiddleware::handle();
        view('auth.change-password');
    }

    public function changePassword(): void {
        AuthMiddleware::handle();
        $userId = Session::userId();

        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (strlen($new) < 8) {
            Flash::error('New password must be at least 8 characters long.');
            redirect('/change-password');
        }

        if ($new !== $confirm) {
            Flash::error('New passwords do not match.');
            redirect('/change-password');
        }

        $user = $this->userModel->findById($userId);
        if (!$user || !password_verify($current, $user['password_hash'])) {
            Flash::error('Current password is incorrect.');
            redirect('/change-password');
        }

        $newHash = password_hash($new, PASSWORD_BCRYPT);
        $this->userModel->update($userId, ['password_hash' => $newHash]);
        AuditLog::record($userId, 'Change Password', 'users', $userId, 'Password changed');

        Flash::success('Password updated successfully.');
        redirect('/');
    }

    public function showProfile(): void {
        AuthMiddleware::handle();
        $userId = Session::userId();
        $user = $this->userModel->findById($userId);

        if (!$user) {
            Flash::error('User profile not found.');
            redirect('/');
            return;
        }

        view('auth.profile', ['user' => $user]);
    }

    public function updateProfile(): void {
        AuthMiddleware::handle();
        $userId = Session::userId();
        $user = $this->userModel->findById($userId);

        if (!$user) {
            Flash::error('User profile not found.');
            redirect('/');
            return;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($fullName) || empty($email)) {
            Flash::error('Full name and email address are required.');
            redirect('/profile');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error('Please enter a valid email address.');
            redirect('/profile');
            return;
        }

        // Check if email changed and if another user has it
        if ($email !== $user['email']) {
            $existing = $this->userModel->findByEmail($email);
            if ($existing && (int)$existing['id'] !== $userId) {
                Flash::error('This email address is already in use by another account.');
                redirect('/profile');
                return;
            }
        }

        $this->userModel->update($userId, [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone
        ]);

        // Refresh session data
        if (isset($_SESSION['user'])) {
            $_SESSION['user']['full_name'] = $fullName;
            $_SESSION['user']['email'] = $email;
        }

        AuditLog::record($userId, 'Update Profile', 'users', $userId, "Updated profile details");
        Flash::success('Profile updated successfully.');
        redirect('/profile');
    }
}


