<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$st = $db->prepare("SELECT us.id, k.name, us.level FROM user_skills us JOIN skills k ON k.id = us.skill_id WHERE us.user_id = ? ORDER BY k.name");
$st->execute([$userId]);
$teach = $st->fetchAll(PDO::FETCH_ASSOC);
$st = $db->prepare("SELECT g.id, k.name, g.target_level, g.priority FROM learning_goals g JOIN skills k ON k.id = g.skill_id WHERE g.user_id = ? ORDER BY k.name");
$st->execute([$userId]);
$learn = $st->fetchAll(PDO::FETCH_ASSOC);
respond(true, ['teach' => $teach, 'learn' => $learn], 'OK');
