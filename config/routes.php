<?php
// config/routes.php

// Auth Routes
$router->get('/login', ['AuthController', 'showLogin'], ['guest']);
$router->post('/login', ['AuthController', 'login'], ['guest', 'csrf']);
$router->get('/logout', ['AuthController', 'logout'], ['auth']);
$router->get('/profile', ['AuthController', 'showProfile'], ['auth']);
$router->post('/profile', ['AuthController', 'updateProfile'], ['auth', 'csrf']);
$router->get('/change-password', ['AuthController', 'showChangePassword'], ['auth']);
$router->post('/change-password', ['AuthController', 'changePassword'], ['auth', 'csrf']);

// Dashboard & Infrastructure
$router->get('/', ['DashboardController', 'index'], ['auth']);
$router->get('/dashboard', ['DashboardController', 'index'], ['auth']);
$router->get('/system/health', ['DashboardController', 'health'], ['auth', 'role:admin']);

// Tickets
$router->get('/tickets', ['TicketController', 'index'], ['auth']);
$router->get('/tickets/create', ['TicketController', 'create'], ['auth']);
$router->post('/tickets', ['TicketController', 'store'], ['auth', 'csrf']);
$router->get('/tickets/{id}', ['TicketController', 'show'], ['auth']);
$router->get('/tickets/{id}/edit', ['TicketController', 'edit'], ['auth']);
$router->post('/tickets/{id}', ['TicketController', 'update'], ['auth', 'csrf']);
$router->post('/tickets/{id}/assign', ['TicketController', 'assign'], ['auth', 'csrf']);
$router->post('/tickets/{id}/status', ['TicketController', 'updateStatus'], ['auth', 'csrf']);
$router->post('/tickets/{id}/comment', ['TicketController', 'addComment'], ['auth', 'csrf']);
$router->post('/tickets/{id}/attachment', ['TicketController', 'uploadAttachment'], ['auth', 'csrf']);
$router->get('/attachments/{id}/download', ['TicketController', 'downloadAttachment'], ['auth']);
$router->post('/attachments/{id}/delete', ['TicketController', 'deleteAttachment'], ['auth', 'csrf']);


// Users (Admin Only)
$router->get('/users', ['UserController', 'index'], ['auth', 'role:admin']);
$router->get('/users/create', ['UserController', 'create'], ['auth', 'role:admin']);
$router->post('/users', ['UserController', 'store'], ['auth', 'csrf', 'role:admin']);
$router->get('/users/{id}/edit', ['UserController', 'edit'], ['auth', 'role:admin']);
$router->post('/users/{id}', ['UserController', 'update'], ['auth', 'csrf', 'role:admin']);
$router->post('/users/{id}/toggle', ['UserController', 'toggleActive'], ['auth', 'csrf', 'role:admin']);

// Departments (Admin Only)
$router->get('/departments', ['DepartmentController', 'index'], ['auth', 'role:admin']);
$router->get('/departments/create', ['DepartmentController', 'create'], ['auth', 'role:admin']);
$router->post('/departments', ['DepartmentController', 'store'], ['auth', 'csrf', 'role:admin']);
$router->get('/departments/{id}/edit', ['DepartmentController', 'edit'], ['auth', 'role:admin']);
$router->post('/departments/{id}', ['DepartmentController', 'update'], ['auth', 'csrf', 'role:admin']);

// Ticket Categories (Admin Only)
$router->get('/categories', ['CategoryController', 'index'], ['auth', 'role:admin']);
$router->get('/categories/create', ['CategoryController', 'create'], ['auth', 'role:admin']);
$router->post('/categories', ['CategoryController', 'store'], ['auth', 'csrf', 'role:admin']);
$router->get('/categories/{id}/edit', ['CategoryController', 'edit'], ['auth', 'role:admin']);
$router->post('/categories/{id}', ['CategoryController', 'update'], ['auth', 'csrf', 'role:admin']);

// IT Assets (Admin, Technician)
$router->get('/assets', ['AssetController', 'index'], ['auth', 'role:admin,technician']);
$router->get('/assets/create', ['AssetController', 'create'], ['auth', 'role:admin']);
$router->post('/assets', ['AssetController', 'store'], ['auth', 'csrf', 'role:admin']);
$router->get('/assets/{id}/edit', ['AssetController', 'edit'], ['auth', 'role:admin']);
$router->post('/assets/{id}', ['AssetController', 'update'], ['auth', 'csrf', 'role:admin']);
$router->post('/assets/{id}/delete', ['AssetController', 'destroy'], ['auth', 'csrf', 'role:admin']);

// Reports (Admin, Manager)
$router->get('/reports', ['ReportController', 'index'], ['auth', 'role:admin,manager']);
$router->get('/reports/export', ['ReportController', 'export'], ['auth', 'role:admin,manager']);

// Audit Logs (Admin Only)
$router->get('/audit-logs', ['AuditLogController', 'index'], ['auth', 'role:admin']);

// Notifications
$router->get('/notifications', ['NotificationController', 'index'], ['auth']);
$router->post('/notifications/{id}/read', ['NotificationController', 'markRead'], ['auth', 'csrf']);
$router->get('/notifications/count', ['NotificationController', 'unreadCount'], ['auth']);

