<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$q = "SELECT r.rating, r.feedback, r.created_at, u.name AS person, k.name AS skill
  FROM reviews r
  JOIN sessions s ON s.id = r.session_id
  JOIN skills k ON k.id = s.skill_id
  JOIN users u ON u.id = r.%s
  WHERE r.%s = ? ORDER BY r.created_at DESC";
$st = $db->prepare(sprintf($q, 'reviewer_id', 'reviewee_id'));
$st->execute([$userId]);
$received = $st->fetchAll(PDO::FETCH_ASSOC);
$st = $db->prepare(sprintf($q, 'reviewee_id', 'reviewer_id'));
$st->execute([$userId]);
$given = $st->fetchAll(PDO::FETCH_ASSOC);
$avg = null;
if (count($received)) $avg = round(array_sum(array_column($received, 'rating')) / count($received), 1);
respond(true, ['received' => $received, 'given' => $given, 'average' => $avg], 'OK');
