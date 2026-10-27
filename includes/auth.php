<?php
function ensureCsrfToken(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrfToken'])) {
        $_SESSION['csrfToken'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrfToken'];
}
function getCsrfToken(): string
{
    return ensureCsrfToken();
}

function isValidCsrfToken(): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $sessionToken = $_SESSION['csrfToken'] ?? '';
    $requestToken = $_POST['csrfToken'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

    if ($sessionToken === '' || $requestToken === '') {
        return false;
    }

    return hash_equals($sessionToken, $requestToken);
}

function requireCsrfTokenJson(): void
{
    if (!isValidCsrfToken()) {
        header('Content-Type: application/json');
        http_response_code(419);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid security token. Refresh the page and try again.'
        ]);
        exit();
    }
}

function isAdminLoggedIn(): bool
{
    return isset($_SESSION['adminId']) && isset($_SESSION['adminUserName']);
}

function requireAdminSessionJson(): void
{
    if (!isAdminLoggedIn()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Please login as admin first.'
        ]);
        exit();
    }
}
