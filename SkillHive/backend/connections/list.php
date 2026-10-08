<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$st = $db->prepare("SELECT c.id, c.status, c.requester_id, c.receiver_id, c.created_at, u.id AS other_id, u.name, u.department, u.year
  FROM connections c JOIN users u ON u.id = IF(c.requester_id = ?, c.receiver_id, c.requester_id)
  WHERE (c.requester_id = ? OR c.receiver_id = ?) AND c.status IN ('pending', 'accepted') ORDER BY c.created_at DESC");
$st->execute([$userId, $userId, $userId]);
$accepted = []; $incoming = []; $outgoing = [];
foreach ($st->fetchAll() as $c) {
  if ($c['status'] === 'accepted') $accepted[] = $c;
  elseif ($c['receiver_id'] == $userId) $incoming[] = $c;
  else $outgoing[] = $c;
}
respond(true, ['accepted' => $accepted, 'incoming' => $incoming, 'outgoing' => $outgoing], 'OK');
