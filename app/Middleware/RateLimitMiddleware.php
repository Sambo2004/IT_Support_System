<?php
/**
 * Rate Limiting Middleware (Session-Based)
 * 
 * Prevents brute force attacks by limiting the number of attempts
 * for a specific action within a time window. Uses the session to
 * track attempts, which is simple and doesn't require a database.
 * 
 * Usage:
 *   if (!RateLimitMiddleware::check('login_' . $username)) {
 *       // Too many attempts — show error
 *   }
 *   RateLimitMiddleware::increment('login_' . $username);
 */
class RateLimitMiddleware
{
    /**
     * Check if the rate limit has been exceeded for a given key.
     *
     * @param string $key   Unique identifier for the action (e.g., 'login_admin')
     * @param int    $max   Maximum number of attempts allowed (default: 5)
     * @param int    $decay Time window in minutes before attempts reset (default: 15)
     * @return bool True if under the limit (allowed), false if over the limit (blocked)
     */
    public static function check(string $key, int $max = 5, int $decay = 15): bool
    {
        // Initialize rate limit tracking array if it doesn't exist
        if (!isset($_SESSION['rate_limit'])) {
            $_SESSION['rate_limit'] = [];
        }

        // If no record for this key, they haven't tried yet — allow
        if (!isset($_SESSION['rate_limit'][$key])) {
            return true;
        }

        $record = $_SESSION['rate_limit'][$key];

        // If the time window has expired, clear the record and allow
        if (time() > $record['expires']) {
            unset($_SESSION['rate_limit'][$key]);
            return true;
        }

        // Check if they've exceeded the maximum attempts
        return $record['count'] < $max;
    }

    /**
     * Increment the attempt counter for a given key.
     * If this is the first attempt, sets the expiration time.
     *
     * @param string $key   Unique identifier for the action
     * @param int    $decay Time window in minutes (default: 15)
     */
    public static function increment(string $key, int $decay = 15): void
    {
        if (!isset($_SESSION['rate_limit'])) {
            $_SESSION['rate_limit'] = [];
        }

        if (!isset($_SESSION['rate_limit'][$key]) || time() > ($_SESSION['rate_limit'][$key]['expires'] ?? 0)) {
            // First attempt or window expired — start fresh
            $_SESSION['rate_limit'][$key] = [
                'count'   => 1,
                'expires' => time() + ($decay * 60),
            ];
        } else {
            // Increment existing counter
            $_SESSION['rate_limit'][$key]['count']++;
        }
    }

    /**
     * Reset (clear) the rate limit counter for a given key.
     * Call this after a successful action (e.g., successful login).
     *
     * @param string $key Unique identifier for the action
     */
    public static function reset(string $key): void
    {
        if (isset($_SESSION['rate_limit'][$key])) {
            unset($_SESSION['rate_limit'][$key]);
        }
    }
}
