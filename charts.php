<?php
// ============================================
// EnsiBeats — Page Charts
// ============================================
require_once __DIR__ . '/php/fonctions.php';

$genre  = isset($_GET['genre']) ? trim($_GET['genre']) : 'tous';
$search = isset($_GET['q'])     ? trim($_GET['q'])     : '';

// Validation du genre
$genresValides = ['tous', 'pop', 'rnb', 'electro', 'rock', 'rap', 'jazz', 'autre'];
if (!in_array($genre, $genresValides)) $genre = 'tous';

if ($search) {
    $chansons = rechercherChansons($search);
} else {
    $chansons = getTopCharts(50, $genre === 'tous' ? '' : $genre);
}

$maxVotes = !empty($chansons) ? $chansons[0]['votes_total'] : 1;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Charts — EnsiBeats</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- NAV -->
<nav class="nav">
  <a class="nav-logo" href="index.php">ENSI<span>BEATS</span></a>
  <div class="nav-links">
    <a href="index.php">Accueil</a>
    <a href="discover.php">Découvrir</a>
    <a href="charts.php" class="active">Charts</a>
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
    <h1>TOP <span class="accent">CHARTS</span></h1>
    <p class="text-muted" style="font-size:0.78rem; margin-top:4px">
      Classement par nombre de votes · Mis à jour en temps réel
    </p>
  </div>
</div>

<!-- CONTENU -->
<div class="section">

  <!-- Recherche -->
  <div class="search-wrap fade-in">
    <input
      type="text"
      id="search-input"
      class="search-input"
      placeholder="Rechercher une chanson ou un artiste…"
      value="<?= htmlspecialchars($search) ?>"
      autocomplete="off"
    >
    <span class="search-icon">⌕</span>
  </div>

  <!-- Filtres genre -->
  <div class="filters fade-in delay-1">
    <?php
    $genres = [
      'tous'   => 'Tous',
      'pop'    => 'Pop',
      'rnb'    => 'R&amp;B',
      'electro'=> 'Électro',
      'rock'   => 'Rock',
      'rap'    => 'Rap',
      'jazz'   => 'Jazz',
    ];
    foreach ($genres as $key => $label):
    ?>
    <button class="filter-btn <?= $genre === $key ? 'active' : '' ?>"
            data-genre="<?= $key ?>">
      <?= $label ?>
    </button>
    <?php endforeach; ?>
  </div>

  <!-- Liste -->
  <div class="track-list">
    <?php if (empty($chansons)): ?>
      <div class="empty-state fade-in">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <p>Aucune chanson trouvée.</p>
      </div>
    <?php else: ?>
      <?php foreach ($chansons as $i => $chanson): ?>
      <div class="track-row"
           style="animation-delay: <?= min($i * 0.04, 1) ?>s"
           data-genre="<?= htmlspecialchars($chanson['genre']) ?>">
        <div class="track-rank"><?= $i + 1 ?></div>
        <div class="track-info">
          <div class="track-name"><?= htmlspecialchars($chanson['titre']) ?></div>
          <div class="track-artist"><?= htmlspecialchars($chanson['artiste']) ?></div>
        </div>
        <div class="track-meta">
          <span class="genre-tag"><?= genreLabel($chanson['genre']) ?></span>
          <span class="track-votes">▲ <?= number_format($chanson['votes_total']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<!-- FOOTER -->
<footer>
  <div class="logo">ENSIBEATS</div>
  <p>© <?= date('Y') ?> EnsiBeats · ENSI Tunis · Fait avec ♪</p>
</footer>

<script src="js/main.js"></script>
</body>
</html>
