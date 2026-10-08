<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/matching.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$matches = computeMatches($db, $userId);
$st = $db->prepare("SELECT requester_id, receiver_id, status FROM connections WHERE requester_id = ? OR receiver_id = ?");
$st->execute([$userId, $userId]);
$map = [];
foreach ($st->fetchAll() as $c) {
  $other = $c['requester_id'] == $userId ? $c['receiver_id'] : $c['requester_id'];
  if ($c['status'] === 'accepted') $map[$other] = 'accepted';
  elseif ($c['status'] === 'pending') $map[$other] = $c['requester_id'] == $userId ? 'pending_sent' : 'pending_received';
}
foreach ($matches as &$m) { $m['connection'] = $map[$m['user_id']] ?? null; }
unset($m);
respond(true, ['matches' => $matches], 'OK');
