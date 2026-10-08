<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$body = getJsonBody();
$sid = (int)($body['session_id'] ?? 0);
$action = $body['action'] ?? '';
$st = $db->prepare("SELECT * FROM sessions WHERE id = ?");
$st->execute([$sid]);
$s = $st->fetch();
if (!$s || ($s['teacher_id'] != $userId && $s['learner_id'] != $userId)) respond(false, null, 'Session not found.');
if ($s['status'] !== 'scheduled') respond(false, null, 'This session is no longer open.');
if ($action === 'cancel') {
  $db->prepare("UPDATE sessions SET status = 'cancelled' WHERE id = ?")->execute([$sid]);
  respond(true, null, 'Session cancelled.');
}
if ($action === 'complete') {
  $col = $s['teacher_id'] == $userId ? 'teacher_confirmed' : 'learner_confirmed';
  $db->prepare("UPDATE sessions SET $col = 1 WHERE id = ?")->execute([$sid]);
  $st->execute([$sid]);
  $s = $st->fetch();
  if ($s['teacher_confirmed'] && $s['learner_confirmed']) {
    $db->prepare("UPDATE sessions SET status = 'completed' WHERE id = ?")->execute([$sid]);
    respond(true, null, 'Session completed! You can now leave a review.');
  }
  respond(true, null, 'Marked as done. Waiting for the other person to confirm.');
}
respond(false, null, 'Unknown action.');
