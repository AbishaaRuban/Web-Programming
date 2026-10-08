<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$st = $db->prepare("SELECT id, name, email, department, year, bio, mode_preference FROM users WHERE id = ?");
$st->execute([$userId]);
respond(true, ['user' => $st->fetch(PDO::FETCH_ASSOC)], 'OK');
