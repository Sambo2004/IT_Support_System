<?php
// app/Controllers/AssetController.php

class AssetController {
    private Asset $assetModel;
    private Department $deptModel;
    private User $userModel;

    public function __construct() {
        AuthMiddleware::handle(['admin', 'technician']);
        $this->assetModel = new Asset();
        $this->deptModel = new Department();
        $this->userModel = new User();
    }

    public function index(): void {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        $assets = $this->assetModel->findAll($filters);
        $categories = $this->assetModel->getCategories();

        view('assets.index', [
            'assets' => $assets,
            'categories' => $categories,
            'filters' => $filters
        ]);
    }

    public function create(): void {
        AuthMiddleware::handle(['admin']);
        $categories = $this->assetModel->getCategories();
        $departments = $this->deptModel->getActive();
        $users = $this->userModel->findAll(['is_active' => 1]);

        view('assets.create', [
            'categories' => $categories,
            'departments' => $departments,
            'users' => $users
        ]);
    }

    public function store(): void {
        AuthMiddleware::handle(['admin']);
        $assetTag = trim($_POST['asset_tag'] ?? '');
        $deviceName = trim($_POST['device_name'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);

        if (empty($assetTag) || empty($deviceName) || !$categoryId) {
            Flash::error('Please fill in required fields: Asset Tag, Device Name, Category.');
            redirect('/assets/create');
        }

        $id = $this->assetModel->create([
            'asset_tag' => $assetTag,
            'device_name' => $deviceName,
            'category_id' => $categoryId,
            'brand' => trim($_POST['brand'] ?? ''),
            'model' => trim($_POST['model'] ?? ''),
            'serial_number' => trim($_POST['serial_number'] ?? ''),
            'purchase_date' => $_POST['purchase_date'] ?: null,
            'warranty_expiry' => $_POST['warranty_expiry'] ?: null,
            'assigned_to' => !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null,
            'department_id' => !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null,
            'location' => trim($_POST['location'] ?? ''),
            'status' => $_POST['status'] ?? 'available',
            'notes' => trim($_POST['notes'] ?? '')
        ]);

        AuditLog::record(Session::userId(), 'Create Asset', 'assets', $id, "Added asset {$assetTag}");
        Flash::success("Asset {$assetTag} registered.");
        redirect('/assets');
    }

    public function edit(string $id): void {
        AuthMiddleware::handle(['admin']);
        $asset = $this->assetModel->findById((int)$id);
        if (!$asset) {
            view('errors.404');
            return;
        }

        $categories = $this->assetModel->getCategories();
        $departments = $this->deptModel->getActive();
        $users = $this->userModel->findAll(['is_active' => 1]);

        view('assets.edit', [
            'asset' => $asset,
            'categories' => $categories,
            'departments' => $departments,
            'users' => $users
        ]);
    }

    public function update(string $id): void {
        AuthMiddleware::handle(['admin']);
        $assetId = (int)$id;
        $asset = $this->assetModel->findById($assetId);
        if (!$asset) {
            view('errors.404');
            return;
        }

        $this->assetModel->update($assetId, [
            'asset_tag' => trim($_POST['asset_tag'] ?? ''),
            'device_name' => trim($_POST['device_name'] ?? ''),
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'brand' => trim($_POST['brand'] ?? ''),
            'model' => trim($_POST['model'] ?? ''),
            'serial_number' => trim($_POST['serial_number'] ?? ''),
            'purchase_date' => $_POST['purchase_date'] ?: null,
            'warranty_expiry' => $_POST['warranty_expiry'] ?: null,
            'assigned_to' => !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null,
            'department_id' => !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null,
            'location' => trim($_POST['location'] ?? ''),
            'status' => $_POST['status'] ?? 'available',
            'notes' => trim($_POST['notes'] ?? '')
        ]);

        AuditLog::record(Session::userId(), 'Update Asset', 'assets', $assetId, "Updated asset details");
        Flash::success("Asset updated.");
        redirect('/assets');
    }

    public function destroy(string $id): void {
        AuthMiddleware::handle(['admin']);
        $assetId = (int)$id;

        $asset = $this->assetModel->findById($assetId);
        if (!$asset) {
            Flash::error("Asset not found.");
            redirect('/assets');
        }

        if (!$this->assetModel->delete($assetId)) {
            Flash::error("Cannot delete asset '{$asset['asset_tag']}': It is associated with existing support tickets. Please change its status to 'Retired' instead.");
            redirect('/assets');
        }

        AuditLog::record(Session::userId(), 'Delete Asset', 'assets', $assetId, "Deleted asset {$asset['asset_tag']}");
        Flash::success("Asset '{$asset['asset_tag']}' deleted successfully.");
        redirect('/assets');
    }
}

