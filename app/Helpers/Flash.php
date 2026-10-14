<?php
/**
 * Flash Message Helper
 * 
 * Flash messages are temporary messages stored in the session that
 * are displayed once and then automatically cleared. They are used
 * to show feedback after redirects (e.g., "Ticket created successfully").
 * 
 * Usage:
 *   Flash::success('Record saved!');   // Set in controller
 *   Flash::get();                      // Retrieve in view (auto-clears)
 */
class Flash
{
    /**
     * Add a success flash message (green).
     *
     * @param string $msg The message to display
     */
    public static function success(string $msg): void
    {
        $_SESSION['flash'][] = ['type' => 'success', 'message' => $msg];
    }

    /**
     * Add an error flash message (red).
     *
     * @param string $msg The message to display
     */
    public static function error(string $msg): void
    {
        $_SESSION['flash'][] = ['type' => 'danger', 'message' => $msg];
    }

    /**
     * Add a warning flash message (yellow).
     *
     * @param string $msg The message to display
     */
    public static function warning(string $msg): void
    {
        $_SESSION['flash'][] = ['type' => 'warning', 'message' => $msg];
    }

    /**
     * Add an info flash message (blue).
     *
     * @param string $msg The message to display
     */
    public static function info(string $msg): void
    {
        $_SESSION['flash'][] = ['type' => 'info', 'message' => $msg];
    }

    /**
     * Get all flash messages and clear them from the session.
     * Call this in the view layout to display messages.
     *
     * @return array Array of ['type' => '...', 'message' => '...'] items
     */
    public static function get(): array
    {
        $messages = $_SESSION['flash'] ?? [];

        // Clear after retrieving — messages show only once
        unset($_SESSION['flash']);

        return $messages;
    }

    /**
     * Check if there are any flash messages waiting to be displayed.
     *
     * @return bool True if flash messages exist
     */
    public static function has(): bool
    {
        return !empty($_SESSION['flash']);
    }
}
