<?php
// app/Controllers/CategoryController.php

class CategoryController {
    private Category $categoryModel;

    public function __construct() {
        AuthMiddleware::handle(['admin']);
        $this->categoryModel = new Category();
    }

    public function index(): void {
        $categories = $this->categoryModel->getWithTicketCount();
        view('categories.index', ['categories' => $categories]);
    }

    public function create(): void {
        view('categories.form', ['category' => null]);
    }

    public function store(): void {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name)) {
            Flash::error('Category name is required.');
            redirect('/categories/create');
        }

        $id = $this->categoryModel->create([
            'name' => $name,
            'description' => $description,
            'is_active' => $isActive
        ]);

        AuditLog::record(Session::userId(), 'Create Category', 'ticket_categories', $id, "Created {$name}");
        Flash::success("Category '{$name}' created.");
        redirect('/categories');
    }

    public function edit(string $id): void {
        $category = $this->categoryModel->findById((int)$id);
        if (!$category) {
            view('errors.404');
            return;
        }
        view('categories.form', ['category' => $category]);
    }

    public function update(string $id): void {
        $catId = (int)$id;
        $category = $this->categoryModel->findById($catId);
        if (!$category) {
            view('errors.404');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name)) {
            Flash::error('Category name is required.');
            redirect("/categories/{$catId}/edit");
        }

        $this->categoryModel->update($catId, [
            'name' => $name,
            'description' => $description,
            'is_active' => $isActive
        ]);

        AuditLog::record(Session::userId(), 'Update Category', 'ticket_categories', $catId, "Updated {$name}");
        Flash::success("Category updated.");
        redirect('/categories');
    }
}

