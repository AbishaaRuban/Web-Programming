<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);

// Local demo: show the reset link on screen because XAMPP cannot send email by default.
// Set this to false once real email sending is configured.
const SHOW_DEV_LINK = true;

$body = getJsonBody();
$email = strtolower(trim($body['email'] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) respond(false, null, 'Enter a valid email address.');

$generic = 'If this email is registered, a reset link has been sent.';

$st = $db->prepare('SELECT id, role FROM users WHERE email = ? LIMIT 1');
$st->execute([$email]);
$u = $st->fetch(PDO::FETCH_ASSOC);
if (!$u) respond(true, null, $generic);

$token = bin2hex(random_bytes(32));
$hash = hash('sha256', $token);

$db->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);
$db->prepare('INSERT INTO password_resets (email, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))')
   ->execute([$email, $hash]);

$link = 'http://localhost/SkillHive/frontend/forgot.html?token=' . $token . '&email=' . urlencode($email);
$msg = "Hello,\n\nUse this link to reset your SkillHive password (valid for 30 minutes):\n$link\n\nIf you did not ask for this, ignore this email.";
@mail($email, 'SkillHive password reset', $msg, "From: no-reply@skillhive.local\r\nContent-Type: text/plain; charset=UTF-8");

// Never show the link on screen for an admin account.
$data = (SHOW_DEV_LINK && $u['role'] !== 'admin') ? ['dev_link' => $link] : null;
respond(true, $data, $generic);