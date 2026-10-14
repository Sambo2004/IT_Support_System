<?php
/**
 * Authentication Middleware
 * 
 * Ensures users are logged in before accessing protected routes.
 * Can also enforce role-based access control (RBAC) by checking
 * if the logged-in user's role matches the required roles.
 */
class AuthMiddleware
{
    /**
     * Check if the user is authenticated and optionally has the required role(s).
     * 
     * If not logged in:
     *   - Stores the intended URL in session (for redirect after login)
     *   - Redirects to the login page
     * 
     * If logged in but role doesn't match:
     *   - Renders a 403 Forbidden error page
     *
     * @param array $roles Optional list of allowed roles (e.g., ['admin', 'manager'])
     */
    public static function handle(array $roles = []): void
    {
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            // Store the page they were trying to visit so we can redirect after login
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];

            Flash::warning('Please log in to continue.');
            redirect('/login');
            exit;
        }

        // If specific roles are required, verify the user's role
        if (!empty($roles)) {
            self::requireRole($roles);
        }
    }

    /**
     * Check if the logged-in user has one of the required roles.
     * Renders a 403 error page if the user's role is not in the allowed list.
     *
     * @param array $roles List of allowed roles (e.g., ['admin'])
     */
    public static function requireRole(array $roles): void
    {
        $userRole = Session::userRole();

        // Check if user's role is in the allowed list
        if (!in_array($userRole, $roles, true)) {
            http_response_code(403);
            // Render a 403 error view if it exists, otherwise show a basic message
            $errorViewPath = BASE_PATH . '/resources/views/errors/403.php';
            if (file_exists($errorViewPath)) {
                require $errorViewPath;
            } else {
                echo '<!DOCTYPE html><html><head><title>403 Forbidden</title>';
                echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">';
                echo '</head><body class="bg-light">';
                echo '<div class="container mt-5"><div class="row justify-content-center">';
                echo '<div class="col-md-6 text-center">';
                echo '<h1 class="display-1 text-danger">403</h1>';
                echo '<h2>Access Denied</h2>';
                echo '<p class="text-muted">You do not have permission to access this page.</p>';
                echo '<a href="' . url('/dashboard') . '" class="btn btn-primary">Back to Dashboard</a>';
                echo '</div></div></div></body></html>';
            }
            exit;
        }
    }
}
