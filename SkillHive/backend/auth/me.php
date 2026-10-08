<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);

$userId = requireAuth();

$stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    respond(false, null, 'User not found');
}

// never send the password hash to the browser
unset($user['password_hash']);

respond(true, $user, 'OK');