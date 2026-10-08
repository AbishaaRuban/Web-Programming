<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
if (session_status() === PHP_SESSION_NONE) session_start();

$body = getJsonBody();
$email = strtolower(trim($body['email'] ?? ''));
$password = $body['password'] ?? '';
$type = (($body['login_type'] ?? 'student') === 'admin') ? 'admin' : 'student';

if ($email === '' || $password === '') respond(false, null, 'Enter your email and password.');

$st = $db->prepare('SELECT id, name, password_hash, role, onboarding_complete FROM users WHERE email = ? LIMIT 1');
$st->execute([$email]);
$u = $st->fetch(PDO::FETCH_ASSOC);

if (!$u || !password_verify($password, $u['password_hash'])) {
    respond(false, null, 'Invalid email or password.');
}

$isAdmin = ($u['role'] === 'admin');
if ($type === 'admin' && !$isAdmin) respond(false, null, 'This account is not an administrator.');
if ($type === 'student' && $isAdmin) respond(false, null, 'Administrators must use the Admin login tab.');

session_regenerate_id(true);
$_SESSION['user_id'] = (int)$u['id'];

if ($isAdmin) {
    $redirect = 'admin.html';
} elseif ((int)$u['onboarding_complete'] === 0) {
    $redirect = 'onboarding.html';
} else {
    $redirect = 'dashboard.html';
}

respond(true, ['role' => $u['role'], 'redirect' => $redirect], 'Login successful.');