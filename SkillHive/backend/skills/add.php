<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$body = getJsonBody();
$type = $body['type'] ?? '';
$name = trim($body['name'] ?? '');
$levels = ['Beginner', 'Intermediate', 'Advanced'];
$prios = ['Low', 'Medium', 'High'];
if (!in_array($type, ['teach', 'learn'], true)) respond(false, null, 'Invalid type.');
if ($name === '' || strlen($name) > 60) respond(false, null, 'Enter a skill name (up to 60 characters).');
$level = in_array($body['level'] ?? '', $levels, true) ? $body['level'] : 'Beginner';
$prio = in_array($body['priority'] ?? '', $prios, true) ? $body['priority'] : 'Medium';

$st = $db->prepare("SELECT id FROM skills WHERE LOWER(name) = LOWER(?)");
$st->execute([$name]);
$skillId = $st->fetchColumn();
if (!$skillId) {
  $db->prepare("INSERT INTO skills (name, created_by) VALUES (?, ?)")->execute([$name, $userId]);
  $skillId = $db->lastInsertId();
}

if ($type === 'teach') {
  $st = $db->prepare("SELECT id FROM user_skills WHERE user_id = ? AND skill_id = ?");
  $st->execute([$userId, $skillId]);
  if ($id = $st->fetchColumn()) {
    $db->prepare("UPDATE user_skills SET level = ? WHERE id = ?")->execute([$level, $id]);
  } else {
    $db->prepare("INSERT INTO user_skills (user_id, skill_id, level) VALUES (?, ?, ?)")->execute([$userId, $skillId, $level]);
  }
} else {
  $st = $db->prepare("SELECT id FROM learning_goals WHERE user_id = ? AND skill_id = ?");
  $st->execute([$userId, $skillId]);
  if ($id = $st->fetchColumn()) {
    $db->prepare("UPDATE learning_goals SET target_level = ?, priority = ? WHERE id = ?")->execute([$level, $prio, $id]);
  } else {
    $db->prepare("INSERT INTO learning_goals (user_id, skill_id, target_level, priority) VALUES (?, ?, ?, ?)")->execute([$userId, $skillId, $level, $prio]);
  }
}
respond(true, null, 'Saved.');
