<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
if (session_status() === PHP_SESSION_NONE) session_start();

$body = getJsonBody();
$name = trim($body['name'] ?? '');
$email = strtolower(trim($body['email'] ?? ''));
$password = $body['password'] ?? '';

if (mb_strlen($name) < 2) respond(false, null, 'Please enter your name.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) respond(false, null, 'Enter a valid email address.');
if (strlen($password) < 8) respond(false, null, 'Password must be at least 8 characters.');

$st = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$st->execute([$email]);
if ($st->fetch()) respond(false, null, 'This email is already registered.');

// SECURITY: the role is always 'user'. Anything the browser sends as "role" is ignored.
$ins = $db->prepare("INSERT INTO users (name, email, password_hash, role, onboarding_complete) VALUES (?, ?, ?, 'user', 0)");
$ins->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);

session_regenerate_id(true);
$_SESSION['user_id'] = (int)$db->lastInsertId();

respond(true, ['redirect' => 'onboarding.html'], 'Account created.');