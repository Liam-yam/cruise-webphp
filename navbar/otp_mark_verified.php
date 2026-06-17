<?php
// ============================================================
// otp_mark_verified.php
// ============================================================
// Called by the signup page's JS after a successful client-side
// OTP verification. Stores a one-time token in the session keyed
// by email so the registration handler in login.php can require
// that the email was actually verified before creating the user.
//
// NOTE: This is a learning/demo pattern. For production you
// should verify the OTP entirely on the server (no client-side
// match), hash the OTP with bcrypt, store it in your DB with
// expiry, and only mark verified after the server-side compare.
// ============================================================

session_start();

header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$email = trim($body['email'] ?? '');
$token = trim($body['token'] ?? '');

if ($email === '' || $token === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing email or token.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid email.']);
    exit();
}

// Persist the token for 5 minutes (matches the OTP TTL).
$_SESSION['otp_verified'][$email] = $token;
$_SESSION['otp_verified_expires'][$email] = time() + 5 * 60;

echo json_encode(['ok' => true]);
