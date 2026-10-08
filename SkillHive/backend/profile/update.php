<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$body = getJsonBody();
$name = trim($body['name'] ?? '');
$dept = trim($body['department'] ?? '');
$year = trim((string)($body['year'] ?? ''));
$bio = trim($body['bio'] ?? '');
$mode = $body['mode_preference'] ?? 'both';
if ($name === '' || strlen($name) > 80) respond(false, null, 'Name is required (up to 80 characters).');
if (!in_array($mode, ['online', 'in-person', 'both'], true)) respond(false, null, 'Invalid mode.');
if (strlen($bio) > 500) respond(false, null, 'Bio is too long (max 500 characters).');
$db->prepare("UPDATE users SET name = ?, department = ?, year = ?, bio = ?, mode_preference = ? WHERE id = ?")
   ->execute([$name, $dept, $year, $bio, $mode, $userId]);
respond(true, null, 'Profile updated.');
