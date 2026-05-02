<?php
// ============================================
// EnsiBeats — Page Découvrir
// ============================================
require_once __DIR__ . '/php/fonctions.php';

$toutesChansons = getTopCharts(50);
$maxVotes = !empty($toutesChansons) ? $toutesChansons[0]['votes_total'] : 1;

// Grouper par genre
$parGenre = [];
foreach ($toutesChansons as $c) {
    $parGenre[$c['genre']][] = $c;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Découvrir — EnsiBeats</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .genre-section { margin-bottom: 3rem; }

    .genre-section-title {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 1rem;
      padding-bottom: 0.75rem;
      border-bottom: 1px solid var(--border);
    }

    .genre-section-title h3 {
      font-family: var(--font-display);
      font-size: 2rem;
      letter-spacing: 1px;
      color: var(--white);
    }

    .genre-section-title .genre-count {
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: var(--muted);
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 100px;
      padding: 4px 12px;
    }

    .stats-bar {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
      margin-bottom: 3rem;
      animation: slide-in 0.5s ease both;
    }

    .stat-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1.25rem;
      text-align: center;
    }

    .stat-card .stat-num {
      font-family: var(--font-display);
      font-size: 2.5rem;
      color: var(--gold);
      line-height: 1;
      margin-bottom: 4px;
    }

    .stat-card .stat-label {
      font-size: 0.68rem;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: var(--muted);
    }

    @media (max-width: 640px) {
      .stats-bar { grid-template-columns: 1fr 1fr; }
    }
  </style>
</head>
<body>

<!-- NAV -->
<nav class="nav">
  <a class="nav-logo" href="index.php">ENSI<span>BEATS</span></a>
  <div class="nav-links">
    <a href="index.php">Accueil</a>
    <a href="discover.php" class="active">Découvrir</a>
    <a href="charts.php">Charts</a>
    <a href="pole.php">Pôle</a>
  </div>
  <div class="nav-live">
    <span class="live-dot"></span>
    Campus en fête
  </div>
</nav>

<!-- PAGE TITLE -->
<div class="page-band fade-in">
  <div>
    <h1>DÉCOUVRIR <span class="accent">LA MUSIQUE</span></h1>
    <p class="text-muted" style="font-size:0.78rem; margin-top:4px">
      Toutes les chansons de l'ENSI, par genre
    </p>
  </div>
</div>

<div class="section">

  <!-- Stats -->
  <?php
    $totalVotesAll = array_sum(array_column($toutesChansons, 'votes_total'));
    $nbChansons    = count($toutesChansons);
    $nbGenres      = count($parGenre);
  ?>
  <div class="stats-bar">
    <div class="stat-card">
      <div class="stat-num"><?= $nbChansons ?></div>
      <div class="stat-label">Chansons</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= $nbGenres ?></div>
      <div class="stat-label">Genres</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= number_format($totalVotesAll) ?></div>
      <div class="stat-label">Votes Total</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= $toutesChansons[0]['titre'] ? mb_strtoupper(mb_substr($toutesChansons[0]['titre'], 0, 6)).'…' : 'N/A' ?></div>
      <div class="stat-label">N°1 du moment</div>
    </div>
  </div>

  <!-- Par genre -->
  <?php
    $genreOrder = ['pop', 'rnb', 'electro', 'rock', 'rap', 'jazz', 'autre'];
    foreach ($genreOrder as $g):
      if (empty($parGenre[$g])) continue;
      $chansonsGenre = $parGenre[$g];
  ?>
  <div class="genre-section fade-in">
    <div class="genre-section-title">
      <h3><?= strtoupper(genreLabel($g)) ?></h3>
      <span class="genre-count"><?= count($chansonsGenre) ?> titres</span>
    </div>

    <div class="discover-grid">
      <?php foreach ($chansonsGenre as $i => $c):
        $pct = round(($c['votes_total'] / $maxVotes) * 100);
      ?>
      <div class="discover-card" style="animation-delay: <?= $i * 0.05 ?>s">
        <span class="genre-tag"><?= genreLabel($c['genre']) ?></span>
        <h4><?= htmlspecialchars($c['titre']) ?></h4>
        <p><?= htmlspecialchars($c['artiste']) ?></p>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px">
          <span style="font-size:0.7rem; color:var(--muted);">▲ <?= number_format($c['votes_total']) ?> votes</span>
          <span style="font-size:0.7rem; color:var(--gold-dim);">#<?= array_search($c, $toutesChansons) + 1 ?> global</span>
        </div>
        <div class="votes-bar-wrap">
          <div class="votes-bar" data-width="<?= $pct ?>"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>

  <?php if (empty($toutesChansons)): ?>
    <div class="empty-state">
      <p>Aucune chanson disponible pour l'instant.</p>
    </div>
  <?php endif; ?>

</div>

<!-- FOOTER -->
<footer>
  <div class="logo">ENSIBEATS</div>
  <p>© <?= date('Y') ?> EnsiBeats · ENSI Tunis · Fait avec ♪</p>
</footer>

<script src="js/main.js"></script>
</body>
</html>
