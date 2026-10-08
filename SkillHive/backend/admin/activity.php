<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);

$userId = requireAuth();

// only admins may see this
$st = $db->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
$st->execute([$userId]);
$role = $st->fetchColumn();
if ($role !== 'admin') {
    respond(false, null, 'Admin only');
}

// who is connected to whom
$connections = $db->query(
    "SELECT c.id, c.status,
            ur.name AS requester_name, ur.email AS requester_email,
            uc.name AS receiver_name,  uc.email AS receiver_email
     FROM connections c
     JOIN users ur ON ur.id = c.requester_id
     JOIN users uc ON uc.id = c.receiver_id
     ORDER BY c.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// sessions: who teaches whom, what, when
$sessions = $db->query(
    "SELECT s.id, s.session_date, s.session_time, s.mode, s.meeting_link, s.status,
            t.name AS teacher_name, l.name AS learner_name, k.name AS skill_name
     FROM sessions s
     JOIN users t ON t.id = s.teacher_id
     JOIN users l ON l.id = s.learner_id
     LEFT JOIN skills k ON k.id = s.skill_id
     ORDER BY s.session_date DESC, s.session_time DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// reviews: who reviewed whom
$reviews = $db->query(
    "SELECT r.id, r.rating, r.feedback,
            rv.name AS reviewer_name, re.name AS reviewee_name, k.name AS skill_name
     FROM reviews r
     JOIN users rv ON rv.id = r.reviewer_id
     JOIN users re ON re.id = r.reviewee_id
     LEFT JOIN sessions s ON s.id = r.session_id
     LEFT JOIN skills k ON k.id = s.skill_id
     ORDER BY r.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

respond(true, [
    'connections' => $connections,
    'sessions'    => $sessions,
    'reviews'     => $reviews
], 'OK');