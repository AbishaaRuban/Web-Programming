<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$q = trim($_GET['q'] ?? '');
if ($q === '') respond(true, [], 'OK');
$like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
$st = $db->prepare("SELECT name FROM skills WHERE name LIKE ? ORDER BY name LIMIT 10");
$st->execute([$like]);
respond(true, $st->fetchAll(PDO::FETCH_COLUMN), 'OK');
