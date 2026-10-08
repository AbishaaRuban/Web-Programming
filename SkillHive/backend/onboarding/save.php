<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$body = getJsonBody();

function findOrCreateSkill($db, $name, $userId) {
  $st = $db->prepare("SELECT id FROM skills WHERE LOWER(name) = LOWER(?)");
  $st->execute([$name]);
  $id = $st->fetchColumn();
  if ($id) return $id;
  $db->prepare("INSERT INTO skills (name, created_by) VALUES (?, ?)")->execute([$name, $userId]);
  return $db->lastInsertId();
}

$dept = trim($body['department'] ?? '');
$year = trim((string)($body['year'] ?? ''));
$bio = trim($body['bio'] ?? '');
$mode = in_array($body['mode_preference'] ?? '', ['online', 'in-person', 'both'], true) ? $body['mode_preference'] : 'online';
$teach = is_array($body['teach'] ?? null) ? $body['teach'] : [];
$learn = is_array($body['learn'] ?? null) ? $body['learn'] : [];
if (!count($teach) || !count($learn)) respond(false, null, 'Add at least one skill you can teach and one you want to learn.');
$levels = ['Beginner', 'Intermediate', 'Advanced'];
$prios = ['Low', 'Medium', 'High'];

try {
  $db->beginTransaction();
  $db->prepare("UPDATE users SET department = ?, year = ?, bio = ?, mode_preference = ?, onboarding_complete = 1 WHERE id = ?")
     ->execute([$dept, $year, $bio, $mode, $userId]);
  $db->prepare("DELETE FROM user_skills WHERE user_id = ?")->execute([$userId]);
  $db->prepare("DELETE FROM learning_goals WHERE user_id = ?")->execute([$userId]);
  foreach ($teach as $t) {
    $n = trim($t['name'] ?? '');
    if ($n === '' || strlen($n) > 60) continue;
    $lv = in_array($t['level'] ?? '', $levels, true) ? $t['level'] : 'Beginner';
    $db->prepare("INSERT IGNORE INTO user_skills (user_id, skill_id, level) VALUES (?, ?, ?)")->execute([$userId, findOrCreateSkill($db, $n, $userId), $lv]);
  }
  foreach ($learn as $t) {
    $n = trim($t['name'] ?? '');
    if ($n === '' || strlen($n) > 60) continue;
    $lv = in_array($t['target_level'] ?? '', $levels, true) ? $t['target_level'] : 'Beginner';
    $pr = in_array($t['priority'] ?? '', $prios, true) ? $t['priority'] : 'Medium';
    $db->prepare("INSERT IGNORE INTO learning_goals (user_id, skill_id, target_level, priority) VALUES (?, ?, ?, ?)")->execute([$userId, findOrCreateSkill($db, $n, $userId), $lv, $pr]);
  }
  $db->commit();
} catch (Throwable $e) {
  if ($db->inTransaction()) $db->rollBack();
  respond(false, null, 'Could not save your profile. Please try again.');
}
respond(true, null, 'Profile saved.');
