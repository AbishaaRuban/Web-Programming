<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$body = getJsonBody();
$type = $body['type'] ?? '';
$id = (int)($body['id'] ?? 0);
if (!$id || !in_array($type, ['teach', 'learn'], true)) respond(false, null, 'Invalid request.');
$table = $type === 'teach' ? 'user_skills' : 'learning_goals';
$db->prepare("DELETE FROM $table WHERE id = ? AND user_id = ?")->execute([$id, $userId]);
respond(true, null, 'Removed.');
