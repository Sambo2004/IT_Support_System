<?php
// app/Controllers/NotificationController.php

class NotificationController {
    private Notification $notifModel;

    public function __construct() {
        AuthMiddleware::handle();
        $this->notifModel = new Notification();
    }

    public function index(): void {
        $userId = Session::userId();
        $notifications = $this->notifModel->getByUserId($userId, 50);
        view('notifications.index', ['notifications' => $notifications]);
    }

    public function markRead(string $id): void {
        $userId = Session::userId();
        $this->notifModel->markAsRead((int)$id, $userId);
        redirect('/notifications');
    }

    public function unreadCount(): void {
        $userId = Session::userId();
        $count = $this->notifModel->getUnreadCount($userId);
        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
        exit;
    }
}

