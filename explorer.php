<?php
$pageTitle = 'Explorer';
require __DIR__ . '/php/header.php';

$genre  = $_GET['genre']  ?? 'tous';
$humeur = $_GET['humeur'] ?? 'tous';
$genresValides = ['tous','pop','rnb','electro','rock','rap','jazz','autre'];
$humeursValides = ['tous','happy','chill','energetic','sad','romantic','hype'];
if (!in_array($genre,  $genresValides))  $genre  = 'tous';
if (!in_array($humeur, $humeursValides)) $humeur = 'tous';

$chansons = getChansons(50, $genre === 'tous' ? '' : $genre, $humeur === 'tous' ? '' : $humeur);
$aVote = $user ? aVoteAujourdhui($user['id']) : false;
?>

<div class="page-band">
  <h1>Explorer <span class="pink">la Musique</span> 🎵</h1>
  <p style="color:var(--muted);font-size:0.85rem;margin-top:6px">
    <?= count($chansons) ?> titres · Filtre par genre ou ambiance
  </p>
</div>

<div class="section">

  <!-- Search -->
  <div class="search-bar fade-in">
    <input type="text" id="search-input" placeholder="Chercher un titre, un artiste…" autocomplete="off">
    <span class="search-icon">🔍</span>
  </div>

  <!-- Filtres Genre -->
  <div style="margin-bottom:0.75rem">
    <p style="font-size:0.72rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:8px">Genre</p>
    <div class="pills">
      <?php foreach (['tous'=>'Tous 🎵','pop'=>'Pop','rnb'=>'R&B','electro'=>'Électro','rock'=>'Rock','rap'=>'Rap','jazz'=>'Jazz'] as $k=>$l): ?>
        <div class="pill <?= $genre===$k?'active':'' ?>" data-filter="<?= $k ?>" data-filter-type="genre"><?= $l ?></div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Filtres Humeur -->
  <div style="margin-bottom:1.5rem">
    <p style="font-size:0.72rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:8px">Ambiance</p>
    <div class="pills">
      <?php foreach (['tous'=>'Toutes','happy'=>'Happy 😊','chill'=>'Chill 😌','energetic'=>'Energetic ⚡','sad'=>'Sad 🥺','romantic'=>'Romantic 💕','hype'=>'Hype 🔥'] as $k=>$l): ?>
        <div class="pill <?= $humeur===$k?'active':'' ?>" data-filter="<?= $k ?>" data-filter-type="humeur"><?= $l ?></div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Résultats de recherche -->
  <div id="search-results"></div>

  <!-- Liste des chansons -->
  <div class="song-grid" id="song-list">
    <?php if (empty($chansons)): ?>
      <div class="empty-state"><p>Aucune chanson trouvée.</p></div>
    <?php else: ?>
      <?php foreach ($chansons as $i => $c): ?>
        <?php $estFav = $user ? estFavori($user['id'], $c['id']) : false; ?>
        <div class="song-card fade-in"
             style="animation-delay:<?= min($i*0.04,1.2) ?>s"
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
            <span class="tag blue"><?= humeurEmoji($c['humeur']) ?></span>
            <span style="font-size:0.8rem;font-weight:700;color:var(--pink)">▲ <?= number_format($c['votes_total']) ?></span>
            <?php if ($user): ?>
              <button class="btn-heart <?= $estFav?'active':'' ?>" data-id="<?= $c['id'] ?>">
                <?= $estFav ? '❤️' : '🤍' ?>
              </button>
              <?php if (!$aVote): ?>
                <button class="btn-vote" data-id="<?= $c['id'] ?>">Voter</button>
              <?php else: ?>
                <button class="btn-vote voted" disabled>✓</button>
              <?php endif; ?>
            <?php else: ?>
              <button class="btn-vote" data-open-auth="login">Voter</button>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Section commentaires (clique sur une chanson) -->
  <?php if (!empty($chansons)): ?>
  <div style="margin-top:3rem">
    <div class="section-header-row">
      <div>
        <span class="section-label">Communauté</span>
        <h2 class="section-title">Commentaires <span class="pink">récents</span> 💬</h2>
      </div>
    </div>

    <?php
      // Charger les 10 derniers commentaires toutes chansons confondues
      $db = getDB();
      $recentComments = $db->query(
        "SELECT cm.contenu, cm.date_commentaire, u.pseudo, u.avatar_couleur, c.titre
         FROM commentaires cm
         JOIN utilisateurs u ON u.id = cm.utilisateur_id
         JOIN chansons c ON c.id = cm.chanson_id
         ORDER BY cm.date_commentaire DESC LIMIT 10"
      )->fetchAll();
    ?>

    <?php if (empty($recentComments)): ?>
      <div class="empty-state">
        <p>Aucun commentaire encore. Sois le premier ! 💬</p>
      </div>
    <?php else: ?>
      <div style="background:white;border-radius:var(--radius-lg);border:2px solid var(--border);padding:1.5rem">
        <?php foreach ($recentComments as $i => $cm): ?>
          <div class="comment-item fade-in" style="animation-delay:<?= $i*0.05 ?>s">
            <div class="avatar-sm" style="background:<?= htmlspecialchars($cm['avatar_couleur']) ?>">
              <?= initialesAvatar($cm['pseudo']) ?>
            </div>
            <div>
              <div class="comment-meta">
                <strong><?= htmlspecialchars($cm['pseudo']) ?></strong>
                sur <em><?= htmlspecialchars($cm['titre']) ?></em>
                · <?= date('d/m H:i', strtotime($cm['date_commentaire'])) ?>
              </div>
              <div class="comment-text"><?= htmlspecialchars($cm['contenu']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Formulaire commentaire rapide -->
    <?php if ($user): ?>
      <div style="margin-top:1rem;background:white;border:2px solid var(--border);border-radius:var(--radius-lg);padding:1.5rem">
        <p style="font-size:0.82rem;font-weight:700;color:var(--muted);margin-bottom:10px">
          Commenter une chanson (indique le titre dans ton message !)
        </p>
        <form id="form-comment" data-chanson="<?= $chansons[0]['id'] ?>" style="display:flex;gap:10px">
          <input type="text" name="contenu" class="form-input" placeholder="Partage ton avis… 🎵" maxlength="300" required>
          <button type="submit" class="btn-pink" style="white-space:nowrap">Envoyer 💬</button>
        </form>
        <div id="comments-list" style="margin-top:10px"></div>
      </div>
    <?php else: ?>
      <div style="text-align:center;margin-top:1rem;padding:1.5rem;background:var(--pink-pale);border-radius:var(--radius-lg)">
        <p style="color:var(--pink);font-weight:600;margin-bottom:10px">Connecte-toi pour commenter et gagner des points ! 💬</p>
        <button class="btn-pink" data-open-auth="login">Se connecter</button>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/php/footer.php'; ?>
