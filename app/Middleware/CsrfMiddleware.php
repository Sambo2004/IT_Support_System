<?php
/**
 * CSRF Protection Middleware
 * 
 * Validates the CSRF token on every POST request. This prevents
 * Cross-Site Request Forgery attacks where a malicious site could
 * trick a user's browser into submitting forms to our application.
 * 
 * Every POST form must include: <?= Csrf::field() ?>
 */
class CsrfMiddleware
{
    /**
     * Verify the CSRF token on POST requests.
     * 
     * Checks the _token field from the POST data against the token
     * stored in the session. If verification fails, the request is
     * rejected with a 403 Forbidden status.
     */
    public static function handle(): void
    {
        // Only check CSRF on POST requests — GET requests don't modify data
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // Get the submitted token from the form
        $token = $_POST['_token'] ?? null;

        // Verify the token
        if (!Csrf::verify($token)) {
            http_response_code(403);
            die(
                '<!DOCTYPE html><html><head><title>403 Forbidden</title>'
                . '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">'
                . '</head><body class="bg-light">'
                . '<div class="container mt-5"><div class="row justify-content-center">'
                . '<div class="col-md-6 text-center">'
                . '<h1 class="display-1 text-danger">403</h1>'
                . '<h2>Invalid Security Token</h2>'
                . '<p class="text-muted">Your session may have expired. Please go back and try again.</p>'
                . '<a href="javascript:history.back()" class="btn btn-primary">Go Back</a>'
                . '</div></div></div></body></html>'
            );
        }
    }
}
