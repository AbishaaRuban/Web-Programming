<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$body = getJsonBody();
$st = $db->prepare("SELECT role FROM users WHERE id = ?");
$st->execute([$userId]);
if ($st->fetchColumn() !== 'admin') respond(false, null, 'Admins only.');
$action = $body['action'] ?? '';
$id = (int)($body['id'] ?? 0);
if (!$id) respond(false, null, 'Missing id.');

if ($action === 'delete_skill') {
  $st = $db->prepare("SELECT (SELECT COUNT(*) FROM user_skills WHERE skill_id = ?) + (SELECT COUNT(*) FROM learning_goals WHERE skill_id = ?) + (SELECT COUNT(*) FROM sessions WHERE skill_id = ?)");
  $st->execute([$id, $id, $id]);
  if ($st->fetchColumn() > 0) respond(false, null, 'This skill is in use, so it cannot be deleted.');
  $db->prepare("DELETE FROM skills WHERE id = ?")->execute([$id]);
  respond(true, null, 'Skill deleted.');
}

if ($action === 'delete_user') {
  if ($id === (int)$userId) respond(false, null, 'You cannot delete your own account.');
  $st = $db->prepare("SELECT role FROM users WHERE id = ?");
  $st->execute([$id]);
  $role = $st->fetchColumn();
  if ($role === false) respond(false, null, 'User not found.');
  if ($role === 'admin') respond(false, null, 'Admin accounts cannot be deleted here.');
  try {
    $db->beginTransaction();
    $run = function ($sql, $p) use ($db) { $db->prepare($sql)->execute($p); };
    $run("DELETE FROM reviews WHERE reviewer_id = ? OR reviewee_id = ? OR session_id IN (SELECT id FROM sessions WHERE teacher_id = ? OR learner_id = ?)", [$id, $id, $id, $id]);
    $run("DELETE FROM sessions WHERE teacher_id = ? OR learner_id = ? OR connection_id IN (SELECT id FROM connections WHERE requester_id = ? OR receiver_id = ?)", [$id, $id, $id, $id]);
    $run("DELETE FROM connections WHERE requester_id = ? OR receiver_id = ?", [$id, $id]);
    $run("DELETE FROM user_skills WHERE user_id = ?", [$id]);
    $run("DELETE FROM learning_goals WHERE user_id = ?", [$id]);
    $run("UPDATE skills SET created_by = ? WHERE created_by = ?", [$userId, $id]);
    $run("DELETE FROM users WHERE id = ?", [$id]);
    $db->commit();
  } catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    respond(false, null, 'Could not delete this user.');
  }
  respond(true, null, 'User deleted.');
}
respond(false, null, 'Unknown action.');
