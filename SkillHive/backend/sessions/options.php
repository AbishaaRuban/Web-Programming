<?php
require_once __DIR__ . '/../config/db.php';
$db = $pdo ?? ($conn ?? null);
$userId = requireAuth();
$st = $db->prepare("SELECT c.id AS connection_id, u.id AS other_id, u.name
  FROM connections c JOIN users u ON u.id = IF(c.requester_id = ?, c.receiver_id, c.requester_id)
  WHERE c.status = 'accepted' AND (c.requester_id = ? OR c.receiver_id = ?)");
$st->execute([$userId, $userId, $userId]);
$out = [];
foreach ($st->fetchAll() as $c) {
  $s = $db->prepare("SELECT us.skill_id, us.user_id AS teacher_id, k.name FROM user_skills us JOIN skills k ON k.id = us.skill_id WHERE us.user_id IN (?, ?) ORDER BY k.name");
  $s->execute([$userId, $c['other_id']]);
  $skills = [];
  foreach ($s->fetchAll() as $r) {
    $skills[] = ['skill_id' => $r['skill_id'], 'teacher_id' => $r['teacher_id'],
      'label' => $r['name'] . ' (taught by ' . ($r['teacher_id'] == $userId ? 'you' : $c['name']) . ')'];
  }
  $c['skills'] = $skills;
  $out[] = $c;
}
respond(true, ['connections' => $out], 'OK');
