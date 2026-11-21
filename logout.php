<?php
session_start();
// 1. Clear all session data held on the server for this request.
$_SESSION = [];

// 2. Expire the session cookie in the browser by resetting it with a past
//    expiry date, reusing the same cookie attributes (path, Secure, HttpOnly,
//    SameSite) so the correct cookie is targeted.
if (ini_get('session.use_cookies')) {
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => $cookie['path'],
        'domain' => $cookie['domain'],
        'secure' => $cookie['secure'],
        'httponly' => $cookie['httponly'],
        'samesite' => $cookie['samesite']
    ]);
}

// 3. Destroy the server session so the old cookie can no longer sign anyone in.
session_destroy();

// 4. Prevent the browser from caching this response, and redirect to login.
//    Combined with the no-cache headers on protected pages, this stops the
//    Back button from restoring an authenticated page after logout.
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Location: login.php');
exit;
