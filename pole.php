<?php
// ============================================
// EnsiBeats — Pôle du Jour (Vote)
// ============================================
require_once __DIR__ . '/php/fonctions.php';

$chansonsVote = getChansonsVote();
$votesJour    = getVotesAujourdhui();
$aVote        = aDejaVote(getIP());
$palmares     = getPalmares(7);

// Total votes aujourd'hui
$totalVotesJour = array_sum($votesJour);

// Trier par votes du jour pour le résultat live
$chansonsAvecVotes = array_map(function($c) use ($votesJour) {
    $c['votes_aujourd_hui'] = $votesJour[$c['id']] ?? 0;
    return $c;
}, $chansonsVote);

$chansonsResultat = $chansonsAvecVotes;
usort($chansonsResultat, fn($a, $b) => $b['votes_aujourd_hui'] - $a['votes_aujourd_hui']);
$maxVoteJour = $chansonsResultat[0]['votes_aujourd_hui'] ?? 1;
if ($maxVoteJour === 0) $maxVoteJour = 1;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pôle du Jour — EnsiBeats</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .results-section { margin-top: 3rem; }

    .result-bar-item {
      margin-bottom: 10px;
      animation: slide-in 0.4s ease both;
    }

    .result-bar-header {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      margin-bottom: 5px;
    }

    .result-bar-title {
      font-family: var(--font-head);
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--white);
    }

    .result-bar-title span {
      color: var(--muted);
      font-weight: 400;
      font-size: 0.75rem;
    }

    .result-bar-count {
      font-size: 0.75rem;
      color: var(--gold);
      font-weight: 600;
    }

    .result-bar-track {
      background: var(--surface2);
      border-radius: 3px;
      height: 6px;
      overflow: hidden;
    }

    .result-bar-fill {
      height: 100%;
      background: linear-gradient(90deg, var(--gold), var(--red));
      border-radius: 3px;
      width: 0%;
      transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .total-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 100px;
      padding: 8px 18px;
      font-size: 0.78rem;
      color: var(--muted);
      margin-bottom: 1.5rem;
    }

    .total-badge strong {
      color: var(--gold);
      font-family: var(--font-head);
    }
  </style>
</head>
<body>

<!-- NAV -->
<nav class="nav">
  <a class="nav-logo" href="index.php">ENSI<span>BEATS</span></a>
  <div class="nav-links">
    <a href="index.php">Accueil</a>
    <a href="discover.php">Découvrir</a>
    <a href="charts.php">Charts</a>
    <a href="pole.php" class="active">Pôle</a>
  </div>
  <div class="nav-live">
    <span class="live-dot"></span>
    Campus en fête
  </div>
</nav>

<!-- PAGE TITLE -->
<div class="page-band fade-in">
  <div>
    <h1>PÔLE DU <span class="accent">JOUR</span></h1>
    <p class="text-muted" style="font-size:0.78rem; margin-top:4px">
      1 vote par personne · Réinitialisation à minuit
    </p>
  </div>
</div>

<div class="section">
  <div class="grid-2">

    <!-- VOTE -->
    <div>
      <div class="section-header">
        <div>
          <p class="section-title">Chanson du jour</p>
          <h2 class="section-headline">Votez <em>maintenant</em></h2>
        </div>
      </div>

      <?php if ($aVote): ?>
      <div class="vote-banner" style="background: linear-gradient(135deg, #1a3a1a 0%, #0d200d 100%); border: 1px solid #2a4a2a;">
        <div class="vote-banner-text">
          <h3 style="color: #6dbd6d">✓ VOTE ENREGISTRÉ !</h3>
          <p>Merci pour votre participation. À demain !</p>
        </div>
        <span class="vote-badge" style="background: rgba(109,189,109,0.15); color: #6dbd6d">Merci !</span>
      </div>
      <?php else: ?>
      <div class="vote-banner">
        <div class="vote-banner-text">
          <h3>VOTEZ POUR LA CHANSON DU JOUR !</h3>
          <p>1 vote par personne · résultats en direct</p>
        </div>
        <span class="vote-badge">Ouvert</span>
      </div>
      <?php endif; ?>

      <div class="vote-list">
        <?php foreach ($chansonsAvecVotes as $i => $chanson): ?>
          <div class="vote-item"
               style="animation-delay: <?= $i * 0.06 ?>s"
               data-id="<?= $chanson['id'] ?>">
            <div class="vote-info">
              <div class="vote-name"><?= htmlspecialchars($chanson['titre']) ?></div>
              <div class="vote-artist">
                <?= htmlspecialchars($chanson['artiste']) ?>
                &nbsp;·&nbsp;
                <span class="genre-tag" style="padding:2px 8px;font-size:0.58rem">
                  <?= genreLabel($chanson['genre']) ?>
                </span>
              </div>
            </div>
            <div class="vote-count"><?= $chanson['votes_aujourd_hui'] ?></div>
            <button class="btn-vote" <?= $aVote ? 'disabled' : '' ?>>
              <?= $aVote ? 'Fermé' : 'Voter' ?>
            </button>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Proposer une chanson -->
      <div class="mt-4">
        <div class="section-header">
          <div>
            <p class="section-title">Tu veux voir ta chanson ici ?</p>
            <h2 class="section-headline">Proposer <em>une chanson</em></h2>
          </div>
        </div>

        <div class="propose-card fade-in delay-2">
          <h3>🎵 Proposition</h3>
          <p>Ta proposition sera examinée avant d'apparaître dans le pôle.</p>

          <form id="form-proposition" novalidate>
            <div class="form-row">
              <input
                type="text"
                name="titre"
                class="form-input"
                placeholder="Titre de la chanson…"
                required
                maxlength="255"
              >
              <input
                type="text"
                name="artiste"
                class="form-input"
                placeholder="Artiste…"
                required
                maxlength="255"
              >
            </div>
            <select name="genre" class="form-input" style="margin-bottom:12px">
              <option value="pop">Pop</option>
              <option value="rnb">R&amp;B</option>
              <option value="electro">Électro</option>
              <option value="rock">Rock</option>
              <option value="rap">Rap</option>
              <option value="jazz">Jazz</option>
              <option value="autre">Autre</option>
            </select>
            <button type="submit" class="btn-primary">→ Proposer</button>
          </form>
        </div>
      </div>
    </div>

    <!-- RÉSULTATS LIVE + PALMARÈS -->
    <div>
      <div class="section-header">
        <div>
          <p class="section-title">En direct</p>
          <h2 class="section-headline">Résultats <em>Live</em></h2>
        </div>
      </div>

      <div class="total-badge">
        <span class="live-dot"></span>
        <span>Total aujourd'hui :</span>
        <strong><?= $totalVotesJour ?> votes</strong>
      </div>

      <div class="results-section">
        <?php foreach ($chansonsResultat as $i => $c): ?>
          <?php $pct = round(($c['votes_aujourd_hui'] / $maxVoteJour) * 100); ?>
          <div class="result-bar-item" style="animation-delay:<?= $i * 0.07 ?>s">
            <div class="result-bar-header">
              <div class="result-bar-title">
                <?= htmlspecialchars($c['titre']) ?>
                <span>· <?= htmlspecialchars($c['artiste']) ?></span>
              </div>
              <div class="result-bar-count"><?= $c['votes_aujourd_hui'] ?> votes</div>
            </div>
            <div class="result-bar-track">
              <div class="result-bar-fill votes-bar" data-width="<?= $pct ?>"></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Palmarès -->
      <div class="mt-4">
        <div class="section-header">
          <div>
            <p class="section-title">Historique</p>
            <h2 class="section-headline">Palmarès <em>de la semaine</em></h2>
          </div>
        </div>

        <div class="palmares-list">
          <?php if (empty($palmares)): ?>
            <div class="empty-state"><p>Pas encore de palmarès.</p></div>
          <?php else: ?>
            <?php
              $jours = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
              foreach ($palmares as $i => $p):
                $ts = strtotime($p['jour']);
                $nomJour = $jours[date('N', $ts) - 1];
            ?>
            <div class="palmares-item fade-in" style="animation-delay:<?= $i * 0.06 ?>s">
              <div class="palmares-day"><?= $nomJour ?></div>
              <div class="palmares-song">
                <?= htmlspecialchars($p['titre']) ?>
                <span>· <?= htmlspecialchars($p['artiste']) ?></span>
              </div>
              <div class="palmares-votes">🔥 <?= number_format($p['nb_votes']) ?></div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
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
