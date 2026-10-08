<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$body = getJsonBody();
$cid = (int)($body['connection_id'] ?? 0);
$action = $body['action'] ?? '';
$st = $db->prepare("SELECT * FROM connections WHERE id = ?");
$st->execute([$cid]);
$c = $st->fetch();
if (!$c || $c['status'] !== 'pending') respond(false, null, 'Request not found or already handled.');
if ($action === 'accept' || $action === 'reject') {
  if ($c['receiver_id'] != $userId) respond(false, null, 'Only the receiver can do this.');
  $db->prepare("UPDATE connections SET status = ? WHERE id = ?")->execute([$action === 'accept' ? 'accepted' : 'rejected', $cid]);
  respond(true, null, $action === 'accept' ? 'Connection accepted.' : 'Request rejected.');
}
if ($action === 'cancel') {
  if ($c['requester_id'] != $userId) respond(false, null, 'Only the sender can cancel.');
  $db->prepare("DELETE FROM connections WHERE id = ?")->execute([$cid]);
  respond(true, null, 'Request cancelled.');
}
respond(false, null, 'Unknown action.');
