<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$st = $db->prepare("SELECT s.id, s.session_date, k.name AS skill,
  IF(s.teacher_id = ?, l.name, t.name) AS other_name
  FROM sessions s
  JOIN skills k ON k.id = s.skill_id
  JOIN users t ON t.id = s.teacher_id
  JOIN users l ON l.id = s.learner_id
  WHERE s.status = 'completed' AND (s.teacher_id = ? OR s.learner_id = ?)
  AND NOT EXISTS (SELECT 1 FROM reviews r WHERE r.session_id = s.id AND r.reviewer_id = ?)
  ORDER BY s.session_date DESC");
$st->execute([$userId, $userId, $userId, $userId]);
respond(true, ['pending' => $st->fetchAll(PDO::FETCH_ASSOC)], 'OK');
