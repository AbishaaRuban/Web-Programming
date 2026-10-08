<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/matching.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$one = function ($sql, $p) use ($db) { $s = $db->prepare($sql); $s->execute($p); return $s->fetchColumn(); };
$matches = computeMatches($db, $userId);
$st = $db->prepare("SELECT s.id, s.session_date, s.session_time, k.name AS skill, IF(s.teacher_id = ?, l.name, t.name) AS other_name
  FROM sessions s JOIN skills k ON k.id = s.skill_id JOIN users t ON t.id = s.teacher_id JOIN users l ON l.id = s.learner_id
  WHERE s.status = 'scheduled' AND (s.teacher_id = ? OR s.learner_id = ?) ORDER BY s.session_date, s.session_time LIMIT 5");
$st->execute([$userId, $userId, $userId]);
$avg = $one("SELECT AVG(rating) FROM reviews WHERE reviewee_id = ?", [$userId]);
respond(true, [
  'matches_count' => count($matches),
  'top_matches' => array_slice($matches, 0, 3),
  'pending_requests' => (int)$one("SELECT COUNT(*) FROM connections WHERE receiver_id = ? AND status = 'pending'", [$userId]),
  'upcoming' => $st->fetchAll(),
  'upcoming_count' => (int)$one("SELECT COUNT(*) FROM sessions WHERE status = 'scheduled' AND (teacher_id = ? OR learner_id = ?)", [$userId, $userId]),
  'completed_count' => (int)$one("SELECT COUNT(*) FROM sessions WHERE status = 'completed' AND (teacher_id = ? OR learner_id = ?)", [$userId, $userId]),
  'avg_rating' => $avg ? round($avg, 1) : null,
  'pending_reviews' => (int)$one("SELECT COUNT(*) FROM sessions s WHERE s.status = 'completed' AND (s.teacher_id = ? OR s.learner_id = ?) AND NOT EXISTS (SELECT 1 FROM reviews r WHERE r.session_id = s.id AND r.reviewer_id = ?)", [$userId, $userId, $userId]),
], 'OK');
