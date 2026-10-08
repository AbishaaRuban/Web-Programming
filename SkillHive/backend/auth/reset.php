<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);

$body = getJsonBody();
$email = strtolower(trim($body['email'] ?? ''));
$token = trim($body['token'] ?? '');
$password = $body['password'] ?? '';

if ($email === '' || $token === '') respond(false, null, 'This reset link is invalid.');
if (strlen($password) < 8) respond(false, null, 'Password must be at least 8 characters.');

$hash = hash('sha256', $token);
$st = $db->prepare('SELECT id FROM password_resets WHERE email = ? AND token_hash = ? AND expires_at > NOW() LIMIT 1');
$st->execute([$email, $hash]);
if (!$st->fetch()) respond(false, null, 'This reset link is invalid or has expired.');

$db->prepare('UPDATE users SET password_hash = ? WHERE email = ?')
   ->execute([password_hash($password, PASSWORD_DEFAULT), $email]);
$db->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);

respond(true, null, 'Password changed. You can log in now.');