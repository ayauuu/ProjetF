<?php
$pageTitle = 'Accueil';
require __DIR__ . '/php/header.php';

$topCharts   = getChansons(8);
$moodUser    = $user ? getMoodAujourdhui($user['id']) : null;
$aVote       = $user ? aVoteAujourdhui($user['id']) : false;
$leaderboard = getLeaderboard(5);
$maxVotes    = !empty($topCharts) ? $topCharts[0]['votes_total'] : 1;
?>

<!-- HERO -->
<section class="hero">
  <div class="floating-notes"></div>

  <div class="hero-badge fade-in">
    <span class="pulse"></span>
    Campus en fête · ENSI Tunis
  </div>

  <h1 class="hero-title fade-in delay-1">
    LA <span class="pink">MUSIQUE</span><br>
    DE L'<span class="outline">ENSI</span>
  </h1>

  <p class="hero-sub fade-in delay-2">
    Découvre, vote, commente — et grimpe dans le classement ! 🚀
  </p>

  <div class="hero-ctas fade-in delay-3">
    <?php if (!$user): ?>
      <button class="btn-pink" data-open-auth="register">Rejoindre la communauté 🎉</button>
      <button class="btn-outline" data-open-auth="login">Se connecter</button>
    <?php else: ?>
      <a href="explorer.php" class="btn-pink">Explorer la musique 🎵</a>
      <a href="mood.php" class="btn-blue">Mon Mood du jour <?= $moodUser ? humeurEmoji($moodUser) : '🎯' ?></a>
    <?php endif; ?>
  </div>

  <div class="visualizer fade-in delay-4" style="margin-top:2.5rem;justify-content:center"></div>
</section>

<!-- CONTENU -->
<div class="section">

  <!-- Stats -->
  <div class="stats-strip fade-in">
    <?php
      $db = getDB();
      $nbChansons = (int)$db->query("SELECT COUNT(*) FROM chansons")->fetchColumn();
      $nbUsers    = (int)$db->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
      $nbVotes    = (int)$db->query("SELECT SUM(votes_total) FROM chansons")->fetchColumn();
      $nbFavoris  = (int)$db->query("SELECT COUNT(*) FROM favoris")->fetchColumn();
    ?>
    <div class="stat-pill pop-in delay-1">
      <span class="stat-num"><?= $nbChansons ?></span>
      <span>chansons</span>
    </div>
    <div class="stat-pill pop-in delay-2">
      <span class="stat-num"><?= $nbUsers ?></span>
      <span>membres</span>
    </div>
    <div class="stat-pill pop-in delay-3">
      <span class="stat-num"><?= number_format($nbVotes) ?></span>
      <span>votes</span>
    </div>
    <div class="stat-pill pop-in delay-4">
      <span class="stat-num"><?= $nbFavoris ?></span>
      <span>favoris</span>
    </div>
  </div>

  <div class="grid-2">

    <!-- TOP CHARTS -->
    <div>
      <div class="section-header-row">
        <div>
          <span class="section-label">Cette semaine</span>
          <h2 class="section-title">Top <span class="pink">Charts</span> 🏆</h2>
        </div>
        <a href="explorer.php" class="btn-outline" style="font-size:0.78rem;padding:8px 18px">Tout voir →</a>
      </div>

      <div class="song-grid">
        <?php foreach ($topCharts as $i => $c): ?>
          <?php
            $estFav = $user ? estFavori($user['id'], $c['id']) : false;
          ?>
          <div class="song-card fade-in" style="animation-delay:<?= $i*0.06 ?>s"
               data-genre="<?= htmlspecialchars($c['genre']) ?>"
               data-humeur="<?= htmlspecialchars($c['humeur']) ?>">
            <div class="song-rank"><?= $i+1 ?></div>
            <div class="song-disc">🎵</div>
            <div class="song-info">
              <div class="song-title"><?= htmlspecialchars($c['titre']) ?></div>
              <div class="song-artist"><?= htmlspecialchars($c['artiste']) ?></div>
            </div>
            <div class="song-meta">
              <span class="tag"><?= genreLabel($c['genre']) ?></span>
              <span style="font-size:0.8rem;font-weight:700;color:var(--pink)">▲ <?= number_format($c['votes_total']) ?></span>
              <?php if ($user): ?>
                <button class="btn-heart <?= $estFav?'active':'' ?>"
                        data-id="<?= $c['id'] ?>">
                  <?= $estFav ? '❤️' : '🤍' ?>
                </button>
                <?php if (!$aVote): ?>
                  <button class="btn-vote" data-id="<?= $c['id'] ?>">Voter</button>
                <?php endif; ?>
              <?php else: ?>
                <button class="btn-vote" data-open-auth="login">Voter</button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- LEADERBOARD -->
    <div>
      <div class="section-header-row">
        <div>
          <span class="section-label">Classement</span>
          <h2 class="section-title">Stars de l'<span class="blue">ENSI</span> ⭐</h2>
        </div>
        <a href="leaderboard.php" class="btn-outline" style="font-size:0.78rem;padding:8px 18px">Tout voir →</a>
      </div>

      <div style="display:flex;flex-direction:column;gap:8px">
        <?php foreach ($leaderboard as $i => $lb): ?>
          <div class="leaderboard-item fade-in" style="animation-delay:<?= $i*0.07 ?>s">
            <div class="lb-rank"><?= $i+1 ?></div>
            <div class="avatar-sm" style="background:<?= htmlspecialchars($lb['avatar_couleur']) ?>">
              <?= initialesAvatar($lb['pseudo']) ?>
            </div>
            <div class="lb-info">
              <div class="lb-pseudo"><?= htmlspecialchars($lb['pseudo']) ?></div>
              <div class="lb-stats">
                ❤️ <?= $lb['nb_favoris'] ?> favoris · 💬 <?= $lb['nb_comments'] ?> comments
              </div>
            </div>
            <div class="lb-points"><?= number_format($lb['points']) ?></div>
          </div>
        <?php endforeach; ?>

        <?php if (empty($leaderboard)): ?>
          <div class="empty-state">
            <p>Sois le premier à gagner des points ! 🚀</p>
            <?php if (!$user): ?>
              <button class="btn-pink" style="margin-top:1rem" data-open-auth="register">Rejoindre</button>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- CTA si pas connecté -->
      <?php if (!$user): ?>
        <div class="card" style="padding:1.5rem;text-align:center;margin-top:1rem;background:linear-gradient(135deg,var(--pink-pale),var(--blue-pale))">
          <div style="font-size:2rem;margin-bottom:8px">🎯</div>
          <h3 style="font-family:var(--font-display);font-size:1.3rem;color:var(--dark);margin-bottom:6px">Gagne des points !</h3>
          <p style="font-size:0.82rem;color:var(--muted);margin-bottom:1rem">Vote (+5), ajoute des favoris (+2), commente (+3)</p>
          <button class="btn-pink" data-open-auth="register">Créer un compte gratuit</button>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/php/footer.php'; ?>
