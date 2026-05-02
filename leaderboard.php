<?php
$pageTitle = 'Classement';
require __DIR__ . '/php/header.php';

$leaderboard = getLeaderboard(20);
$myRank = null;
if ($user) {
    foreach ($leaderboard as $i => $lb) {
        if ($lb['id'] === $user['id']) { $myRank = $i + 1; break; }
    }
}

$pointsSystem = [
  ['icon'=>'🗳️', 'action'=>'Voter pour une chanson',  'points'=>'+5 pts'],
  ['icon'=>'❤️', 'action'=>'Ajouter un favori',        'points'=>'+2 pts'],
  ['icon'=>'💬', 'action'=>'Écrire un commentaire',    'points'=>'+3 pts'],
];
?>

<div class="page-band">
  <h1>Classement <span class="blue">EnsiBeats</span> 🏆</h1>
  <p style="color:var(--muted);font-size:0.85rem;margin-top:6px">
    Les membres les plus actifs de la communauté
  </p>
</div>

<div class="section">
  <div class="grid-2">

    <!-- LEADERBOARD COMPLET -->
    <div>
      <?php if ($user && $myRank): ?>
        <div style="background:linear-gradient(135deg,var(--pink),var(--blue));border-radius:var(--radius-lg);padding:1.25rem 1.5rem;color:white;margin-bottom:1.25rem;display:flex;align-items:center;gap:1rem">
          <div style="font-family:var(--font-display);font-size:2.5rem;line-height:1">#<?= $myRank ?></div>
          <div>
            <div style="font-weight:700;font-size:1rem">Ta position dans le classement</div>
            <div style="opacity:0.8;font-size:0.82rem"><?= number_format($user['points']) ?> points · Continue comme ça ! 🚀</div>
          </div>
        </div>
      <?php elseif (!$user): ?>
        <div style="background:var(--pink-pale);border:2px solid var(--border);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:1rem;justify-content:space-between">
          <div>
            <div style="font-weight:700;color:var(--dark)">Rejoins le classement ! 🏆</div>
            <div style="font-size:0.8rem;color:var(--muted)">Inscris-toi et commence à gagner des points</div>
          </div>
          <button class="btn-pink" data-open-auth="register">Rejoindre</button>
        </div>
      <?php endif; ?>

      <div style="display:flex;flex-direction:column;gap:8px">
        <?php foreach ($leaderboard as $i => $lb): ?>
          <?php $isMe = $user && $lb['id'] === $user['id']; ?>
          <div class="leaderboard-item fade-in <?= $isMe?'':''; ?>"
               style="animation-delay:<?= $i*0.05 ?>s;<?= $isMe?'border-color:var(--pink);background:var(--pink-pale);':'' ?>">
            <div class="lb-rank"><?= $i+1 ?></div>
            <div class="avatar-sm" style="background:<?= htmlspecialchars($lb['avatar_couleur']) ?>">
              <?= initialesAvatar($lb['pseudo']) ?>
            </div>
            <div class="lb-info">
              <div class="lb-pseudo">
                <?= htmlspecialchars($lb['pseudo']) ?>
                <?= $isMe ? '<span style="font-size:0.7rem;color:var(--pink);font-weight:700"> · Toi !</span>' : '' ?>
                <?php if ($i === 0): ?> 👑<?php elseif ($i === 1): ?> 🥈<?php elseif ($i === 2): ?> 🥉<?php endif; ?>
              </div>
              <div class="lb-stats">
                ❤️ <?= $lb['nb_favoris'] ?> favoris · 💬 <?= $lb['nb_comments'] ?> commentaires
                · 🎵 <?= genreLabel($lb['genre_favori']) ?>
              </div>
            </div>
            <div class="lb-points"><?= number_format($lb['points']) ?></div>
          </div>
        <?php endforeach; ?>

        <?php if (empty($leaderboard)): ?>
          <div class="empty-state">
            <p>Le classement est vide. Sois le premier ! 🚀</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- SYSTÈME DE POINTS -->
    <div>
      <span class="section-label">Comment gagner</span>
      <h2 class="section-title">Système de <span class="pink">Points</span> 🎯</h2>

      <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:2rem">
        <?php foreach ($pointsSystem as $p): ?>
          <div style="background:white;border:2px solid var(--border);border-radius:var(--radius);padding:1rem 1.25rem;display:flex;align-items:center;gap:1rem;transition:all 0.2s"
               onmouseover="this.style.borderColor='var(--pink-light)'"
               onmouseout="this.style.borderColor='var(--border)'">
            <span style="font-size:1.8rem"><?= $p['icon'] ?></span>
            <div style="flex:1">
              <div style="font-weight:700;font-size:0.9rem;color:var(--dark)"><?= $p['action'] ?></div>
            </div>
            <span style="font-family:var(--font-display);font-size:1.3rem;background:linear-gradient(135deg,var(--pink),var(--blue));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;white-space:nowrap">
              <?= $p['points'] ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Top genres stats -->
      <span class="section-label">Stats du campus</span>
      <h2 class="section-title" style="font-size:1.6rem">Genres <span class="blue">populaires</span> 📈</h2>

      <?php
        $db = getDB();
        $genreStats = $db->query(
          "SELECT genre, COUNT(*) as nb, SUM(votes_total) as total_votes
           FROM chansons GROUP BY genre ORDER BY total_votes DESC"
        )->fetchAll();
        $maxGenreVotes = !empty($genreStats) ? $genreStats[0]['total_votes'] : 1;
      ?>

      <div style="background:white;border:2px solid var(--border);border-radius:var(--radius-lg);padding:1.5rem">
        <?php foreach ($genreStats as $g): ?>
          <?php $pct = $maxGenreVotes > 0 ? round($g['total_votes'] / $maxGenreVotes * 100) : 0; ?>
          <div style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;margin-bottom:5px">
              <span style="font-weight:700;font-size:0.88rem"><?= genreLabel($g['genre']) ?></span>
              <span style="font-size:0.75rem;color:var(--muted)"><?= number_format($g['total_votes']) ?> votes · <?= $g['nb'] ?> titres</span>
            </div>
            <div style="background:var(--pink-pale);border-radius:4px;height:8px;overflow:hidden">
              <div style="width:<?= $pct ?>%;height:100%;background:linear-gradient(90deg,var(--pink),var(--blue));border-radius:4px"></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<?php require __DIR__ . '/php/footer.php'; ?>
