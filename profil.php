<?php
$pageTitle = 'Mon Profil';
require __DIR__ . '/php/header.php';

// Redirige si pas connecté
if (!$user) {
    header('Location: index.php');
    exit;
}

$favoris     = getFavoris($user['id']);
$moodToday   = getMoodAujourdhui($user['id']);
$aVote       = aVoteAujourdhui($user['id']);

$moods = [
  'happy'=>'😊','chill'=>'😌','energetic'=>'⚡','sad'=>'🥺','romantic'=>'💕','hype'=>'🔥'
];

// Rang dans le leaderboard
$db = getDB();
$stmt = $db->prepare(
  "SELECT COUNT(*)+1 as rang FROM utilisateurs WHERE points > :p"
);
$stmt->execute([':p' => $user['points']]);
$rang = (int)$stmt->fetchColumn();

// Stats perso
$nbVotes    = (int)$db->prepare("SELECT COUNT(*) FROM votes_jour WHERE utilisateur_id=:id")
                       ->execute([':id'=>$user['id']]) ? $db->query("SELECT COUNT(*) FROM votes_jour WHERE utilisateur_id={$user['id']}")->fetchColumn() : 0;
$nbComments = (int)$db->query("SELECT COUNT(*) FROM commentaires WHERE utilisateur_id={$user['id']}")->fetchColumn();
$nbFavoris  = count($favoris);
?>

<div class="page-band" style="background:linear-gradient(135deg,var(--pink-pale),var(--blue-pale))">
  <h1>Mon <span class="pink">Profil</span> 🎵</h1>
</div>

<div class="section">

  <!-- PROFILE HERO -->
  <div class="profile-hero fade-in">
    <div class="avatar-lg" style="background:<?= htmlspecialchars($user['avatar_couleur']) ?>">
      <?= initialesAvatar($user['pseudo']) ?>
    </div>
    <div style="flex:1;min-width:0">
      <div class="profile-name"><?= htmlspecialchars($user['pseudo']) ?></div>
      <div class="profile-bio">
        <?= $user['bio'] ? htmlspecialchars($user['bio']) : 'Pas encore de bio — ajoutes-en une !' ?>
      </div>
      <div class="profile-stats">
        <div class="profile-stat">
          <div class="num"><?= number_format($user['points']) ?></div>
          <div class="lbl">Points</div>
        </div>
        <div class="profile-stat">
          <div class="num">#<?= $rang ?></div>
          <div class="lbl">Rang</div>
        </div>
        <div class="profile-stat">
          <div class="num"><?= $nbFavoris ?></div>
          <div class="lbl">Favoris</div>
        </div>
        <div class="profile-stat">
          <div class="num"><?= $nbComments ?></div>
          <div class="lbl">Commentaires</div>
        </div>
      </div>
    </div>
    <?php if ($moodToday): ?>
      <div style="text-align:center;opacity:0.9">
        <div style="font-size:2.5rem"><?= $moods[$moodToday] ?? '🎵' ?></div>
        <div style="font-size:0.72rem;letter-spacing:1px;text-transform:uppercase;margin-top:4px">Mood du jour</div>
      </div>
    <?php endif; ?>
  </div>

  <div class="grid-2">

    <!-- FAVORIS -->
    <div>
      <span class="section-label">Ma collection</span>
      <h2 class="section-title">Mes <span class="pink">Favoris</span> ❤️</h2>

      <?php if (empty($favoris)): ?>
        <div class="empty-state" style="background:white;border:2px solid var(--border);border-radius:var(--radius-lg);padding:3rem">
          <div style="font-size:3rem;margin-bottom:1rem">🤍</div>
          <p style="color:var(--muted);margin-bottom:1rem">Tu n'as pas encore de favoris.</p>
          <a href="explorer.php" class="btn-pink">Explorer la musique</a>
        </div>
      <?php else: ?>
        <div class="song-grid">
          <?php foreach ($favoris as $i => $f): ?>
            <div class="song-card fade-in" style="animation-delay:<?= $i*0.05 ?>s">
              <div class="song-disc">🎵</div>
              <div class="song-info">
                <div class="song-title"><?= htmlspecialchars($f['titre']) ?></div>
                <div class="song-artist"><?= htmlspecialchars($f['artiste']) ?></div>
              </div>
              <div class="song-meta">
                <span class="tag"><?= genreLabel($f['genre']) ?></span>
                <button class="btn-heart active" data-id="<?= $f['id'] ?>">❤️</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- EDITER PROFIL -->
    <div>
      <span class="section-label">Personnalisation</span>
      <h2 class="section-title">Modifier mon <span class="blue">Profil</span> ✏️</h2>

      <div style="background:white;border:2px solid var(--border);border-radius:var(--radius-lg);padding:1.75rem">
        <form id="form-profil">
          <div class="form-group">
            <label class="form-label">Ma bio</label>
            <textarea name="bio" class="form-input" rows="3" maxlength="200"
              placeholder="Dis-nous qui tu es, ton genre musical préféré…"
              style="resize:vertical"><?= htmlspecialchars($user['bio']) ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Genre musical favori</label>
            <select name="genre_favori" class="form-input">
              <?php foreach (['pop'=>'Pop','rnb'=>'R&B','electro'=>'Électro','rock'=>'Rock','rap'=>'Rap','jazz'=>'Jazz','autre'=>'Autre'] as $k=>$l): ?>
                <option value="<?= $k ?>" <?= $user['genre_favori']===$k?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn-pink" style="width:100%">Sauvegarder ✅</button>
        </form>
      </div>

      <!-- Mes stats détaillées -->
      <div style="margin-top:1.5rem;background:linear-gradient(135deg,var(--pink-pale),var(--blue-pale));border-radius:var(--radius-lg);padding:1.5rem">
        <h3 style="font-family:var(--font-display);font-size:1.4rem;color:var(--dark);margin-bottom:1rem">
          Mes activités 📊
        </h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <?php
            $activities = [
              ['icon'=>'🗳️','label'=>'Votes','val'=>$nbVotes],
              ['icon'=>'❤️','label'=>'Favoris','val'=>$nbFavoris],
              ['icon'=>'💬','label'=>'Commentaires','val'=>$nbComments],
              ['icon'=>'🏆','label'=>'Points total','val'=>number_format($user['points'])],
            ];
            foreach ($activities as $a):
          ?>
          <div style="background:white;border-radius:var(--radius);padding:1rem;text-align:center;border:2px solid rgba(255,45,120,0.1)">
            <div style="font-size:1.4rem;margin-bottom:4px"><?= $a['icon'] ?></div>
            <div style="font-family:var(--font-display);font-size:1.6rem;color:var(--pink);line-height:1"><?= $a['val'] ?></div>
            <div style="font-size:0.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;margin-top:2px"><?= $a['label'] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Déconnexion -->
      <div style="margin-top:1rem;text-align:center">
        <button data-logout class="btn-ghost" style="color:var(--muted);font-size:0.82rem">
          Se déconnecter →
        </button>
      </div>
    </div>

  </div>
</div>

<?php require __DIR__ . '/php/footer.php'; ?>
