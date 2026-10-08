<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$st = $db->prepare("SELECT role FROM users WHERE id = ?");
$st->execute([$userId]);
if ($st->fetchColumn() !== 'admin') respond(false, null, 'Admins only.');
$users = $db->query("SELECT id, name, email, department, role, created_at FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$skills = $db->query("SELECT k.id, k.name, (SELECT COUNT(*) FROM user_skills WHERE skill_id = k.id) AS teachers, (SELECT COUNT(*) FROM learning_goals WHERE skill_id = k.id) AS learners FROM skills k ORDER BY k.name")->fetchAll(PDO::FETCH_ASSOC);
$stats = [
  'users' => count($users),
  'skills' => count($skills),
  'sessions' => (int)$db->query("SELECT COUNT(*) FROM sessions")->fetchColumn(),
  'reviews' => (int)$db->query("SELECT COUNT(*) FROM reviews")->fetchColumn(),
];
respond(true, ['users' => $users, 'skills' => $skills, 'stats' => $stats], 'OK');
