<?php
/**
 * CSRF Token Management Helper
 * 
 * Protects against Cross-Site Request Forgery attacks by generating
 * a unique token per session that must be included in all POST forms.
 * The token is verified on every POST request by CsrfMiddleware.
 */
class Csrf
{
    /**
     * Get the current CSRF token, or generate a new one if none exists.
     * This is the primary method to call when you need a token.
     *
     * @return string The CSRF token
     */
    public static function token(): string
    {
        // Return existing token if one is already in the session
        if (!empty($_SESSION['csrf_token'])) {
            return $_SESSION['csrf_token'];
        }

        // No token exists, generate a fresh one
        return self::generate();
    }

    /**
     * Generate a hidden HTML input field containing the CSRF token.
     * Include this in every POST form: <?= Csrf::field() ?>
     *
     * @return string HTML hidden input element
     */
    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . self::token() . '">';
    }

    /**
     * Verify a submitted token against the session token.
     * Uses hash_equals() for timing-safe comparison to prevent
     * timing attacks that could guess the token.
     *
     * @param string|null $token The token from the POST request
     * @return bool True if the token is valid
     */
    public static function verify(?string $token): bool
    {
        // If no token was submitted or no token in session, fail
        if ($token === null || empty($_SESSION['csrf_token'])) {
            return false;
        }

        // Timing-safe comparison
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Generate a new CSRF token and store it in the session.
     * Uses cryptographically secure random bytes.
     *
     * @return string The newly generated token
     */
    public static function generate(): string
    {
        // 32 random bytes = 64 hex characters
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;

        return $token;
    }
}
