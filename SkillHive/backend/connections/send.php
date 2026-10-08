<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$body = getJsonBody();
$rid = (int)($body['receiver_id'] ?? 0);
if (!$rid || $rid == $userId) respond(false, null, 'Invalid user.');
$st = $db->prepare("SELECT id FROM users WHERE id = ?");
$st->execute([$rid]);
if (!$st->fetch()) respond(false, null, 'User not found.');
$st = $db->prepare("SELECT id, status FROM connections WHERE (requester_id = ? AND receiver_id = ?) OR (requester_id = ? AND receiver_id = ?)");
$st->execute([$userId, $rid, $rid, $userId]);
$row = $st->fetch();
if ($row) {
  if ($row['status'] === 'pending') respond(false, null, 'A request already exists between you two.');
  if ($row['status'] === 'accepted') respond(false, null, 'You are already connected.');
  $db->prepare("DELETE FROM connections WHERE id = ?")->execute([$row['id']]);
}
$db->prepare("INSERT INTO connections (requester_id, receiver_id) VALUES (?, ?)")->execute([$userId, $rid]);
respond(true, null, 'Connection request sent.');
