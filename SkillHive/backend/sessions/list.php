<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$st = $db->prepare("SELECT s.id, s.session_date, s.session_time, s.mode, s.meeting_link, s.status, s.teacher_id, s.learner_id,
  s.teacher_confirmed, s.learner_confirmed, k.name AS skill, t.name AS teacher_name, l.name AS learner_name,
  EXISTS (SELECT 1 FROM reviews r WHERE r.session_id = s.id AND r.reviewer_id = ?) AS reviewed
  FROM sessions s JOIN skills k ON k.id = s.skill_id JOIN users t ON t.id = s.teacher_id JOIN users l ON l.id = s.learner_id
  WHERE s.teacher_id = ? OR s.learner_id = ? ORDER BY s.session_date DESC, s.session_time DESC");
$st->execute([$userId, $userId, $userId]);
$out = [];
foreach ($st->fetchAll() as $s) {
  $isTeacher = $s['teacher_id'] == $userId;
  $s['role'] = $isTeacher ? 'teacher' : 'learner';
  $s['other_name'] = $isTeacher ? $s['learner_name'] : $s['teacher_name'];
  $s['my_confirmed'] = (int)($isTeacher ? $s['teacher_confirmed'] : $s['learner_confirmed']);
  $s['other_confirmed'] = (int)($isTeacher ? $s['learner_confirmed'] : $s['teacher_confirmed']);
  $s['reviewed'] = (int)$s['reviewed'];
  $out[] = $s;
}
respond(true, ['sessions' => $out], 'OK');
