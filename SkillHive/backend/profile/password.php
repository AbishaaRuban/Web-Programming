<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$body = getJsonBody();
$cur = $body['current_password'] ?? '';
$new = $body['new_password'] ?? '';
if (strlen($new) < 8) respond(false, null, 'New password must be at least 8 characters.');
$st = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
$st->execute([$userId]);
$hash = $st->fetchColumn();
if (!$hash || !password_verify($cur, $hash)) respond(false, null, 'Current password is wrong.');
$db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([password_hash($new, PASSWORD_DEFAULT), $userId]);
respond(true, null, 'Password changed.');
