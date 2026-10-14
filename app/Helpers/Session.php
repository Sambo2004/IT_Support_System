<?php
/**
 * Session Management Helper
 * 
 * Provides a clean static API for session management with secure defaults.
 * All session interactions should go through this class to maintain
 * consistent behavior and security settings across the application.
 */
class Session
{
    /**
     * Start the session with secure settings.
     * Called once at the beginning of every request in index.php.
     */
    public static function start(): void
    {
        // Only start if no session is active
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Configure secure session settings before starting
        ini_set('session.cookie_httponly', '1');       // Prevent JavaScript access to session cookie
        ini_set('session.cookie_samesite', 'Strict');  // Prevent CSRF via cross-site requests
        ini_set('session.use_strict_mode', '1');       // Reject uninitialized session IDs
        ini_set('session.use_only_cookies', '1');      // Only use cookies for session (no URL params)

        $secureCookie = filter_var($_ENV['SESSION_SECURE_COOKIE'] ?? false, FILTER_VALIDATE_BOOLEAN)
            || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        if ($secureCookie) {
            ini_set('session.cookie_secure', '1');
        }

        // Set a custom session name for our application
        session_name('IT_SUPPORT_SID');

        session_start();
    }

    /**
     * Set a value in the session.
     *
     * @param string $key   The session key
     * @param mixed  $value The value to store
     */
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a value from the session.
     *
     * @param string $key     The session key
     * @param mixed  $default Default value if key doesn't exist
     * @return mixed The session value or default
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if a key exists in the session.
     *
     * @param string $key The session key to check
     * @return bool True if the key exists
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a value from the session.
     *
     * @param string $key The session key to remove
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Destroy the entire session.
     * Used during logout to completely clear all session data.
     */
    public static function destroy(): void
    {
        // Clear all session data
        $_SESSION = [];

        // Delete the session cookie from the browser
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy the session on the server
        session_destroy();
    }

    /**
     * Regenerate the session ID.
     * Important to call after login to prevent session fixation attacks.
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Check if a user is currently logged in.
     *
     * @return bool True if user_id exists in session
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get the current logged-in user's data.
     *
     * @return array|null User data array or null if not logged in
     */
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Get the current logged-in user's ID.
     *
     * @return int|null User ID or null if not logged in
     */
    public static function userId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    /**
     * Get the current logged-in user's role.
     *
     * @return string|null User role or null if not logged in
     */
    public static function userRole(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    /**
     * Store user data in the session after successful login.
     * Only stores essential user fields to keep the session lightweight.
     *
     * @param array $user Full user row from the database
     */
    public static function setUser(array $user): void
    {
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user'] = [
            'id'            => (int)$user['id'],
            'username'      => $user['username'],
            'full_name'     => $user['full_name'],
            'email'         => $user['email'],
            'role'          => $user['role'],
            'department_id' => $user['department_id'] ? (int)$user['department_id'] : null,
        ];
    }
}
