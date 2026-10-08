<?php
function levelRank($l) { $m = ['Beginner' => 1, 'Intermediate' => 2, 'Advanced' => 3]; return $m[$l] ?? 1; }

function computeMatches($db, $userId, $limit = null) {
  $st = $db->prepare("SELECT mode_preference FROM users WHERE id = ?");
  $st->execute([$userId]);
  $myMode = $st->fetchColumn();
  $teachBy = []; $goalsBy = [];
  foreach ($db->query("SELECT us.user_id, us.skill_id, us.level, k.name FROM user_skills us JOIN skills k ON k.id = us.skill_id") as $r) {
    $teachBy[$r['user_id']][$r['skill_id']] = ['name' => $r['name'], 'level' => $r['level']];
  }
  foreach ($db->query("SELECT g.user_id, g.skill_id, g.target_level, k.name FROM learning_goals g JOIN skills k ON k.id = g.skill_id") as $r) {
    $goalsBy[$r['user_id']][$r['skill_id']] = ['name' => $r['name'], 'target' => $r['target_level']];
  }
  $myTeach = $teachBy[$userId] ?? [];
  $myGoals = $goalsBy[$userId] ?? [];
  $st = $db->prepare("SELECT id, name, department, year, bio, mode_preference FROM users WHERE id <> ? AND onboarding_complete = 1");
  $st->execute([$userId]);
  $out = [];
  foreach ($st->fetchAll() as $u) {
    $uid = $u['id'];
    $theyTeach = []; $iTeach = []; $fits = [];
    foreach ($myGoals as $sid => $g) {
      if (isset($teachBy[$uid][$sid])) {
        $t = $teachBy[$uid][$sid];
        $theyTeach[] = $t['name'] . ' (' . $t['level'] . ')';
        $fits[] = levelRank($t['level']) >= levelRank($g['target']) ? 1 : 0.5;
      }
    }
    foreach (($goalsBy[$uid] ?? []) as $sid => $g) {
      if (isset($myTeach[$sid])) {
        $iTeach[] = $myTeach[$sid]['name'] . ' (' . $myTeach[$sid]['level'] . ')';
        $fits[] = levelRank($myTeach[$sid]['level']) >= levelRank($g['target']) ? 1 : 0.5;
      }
    }
    if (!$theyTeach && !$iTeach) continue;
    $twoWay = $theyTeach && $iTeach;
    $score = $twoWay ? 60 : 30;
    $score += (int)round(25 * array_sum($fits) / count($fits));
    $modeOk = ($myMode === $u['mode_preference'] || $myMode === 'both' || $u['mode_preference'] === 'both');
    if ($modeOk) $score += 15;
    $reasons = [];
    if ($theyTeach) $reasons[] = $u['name'] . ' can teach you: ' . implode(', ', $theyTeach);
    if ($iTeach) $reasons[] = 'You can teach ' . $u['name'] . ': ' . implode(', ', $iTeach);
    $reasons[] = $modeOk ? 'Compatible learning mode' : 'Different learning modes';
    $out[] = [
      'user_id' => $uid, 'name' => $u['name'], 'department' => $u['department'], 'year' => $u['year'],
      'bio' => $u['bio'], 'mode_preference' => $u['mode_preference'], 'score' => $score,
      'match_type' => $twoWay ? 'two-way' : 'one-way', 'reasons' => $reasons,
    ];
  }
  usort($out, function ($a, $b) { return $b['score'] <=> $a['score']; });
  return $limit ? array_slice($out, 0, $limit) : $out;
}
