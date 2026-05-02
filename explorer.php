<?php
$pageTitle = 'Explorer';
require __DIR__ . '/php/header.php';
require __DIR__ . '/php/player.php';

$genre  = $_GET['genre']  ?? 'tous';
$humeur = $_GET['humeur'] ?? 'tous';
$genresValides  = ['tous','pop','rnb','electro','rock','rap','jazz','autre'];
$humeursValides = ['tous','happy','chill','energetic','sad','romantic','hype'];
if (!in_array($genre,  $genresValides))  $genre  = 'tous';
if (!in_array($humeur, $humeursValides)) $humeur = 'tous';

$chansons = getChansons(50, $genre === 'tous' ? '' : $genre, $humeur === 'tous' ? '' : $humeur);
$aVote    = $user ? aVoteAujourdhui($user['id']) : false;
?>

<!-- ═══ MINI PLAYER FIXÉ EN BAS ═══ -->
<div id="player-bar" style="
  position:fixed; bottom:0; left:0; right:0; z-index:500;
  background:white;
  border-top:2px solid var(--border);
  box-shadow:0 -8px 40px rgba(255,45,120,0.18);
  padding:12px 2rem;
  display:none;
  align-items:center;
  gap:1.25rem;
">
  <!-- Disque + Infos -->
  <div style="display:flex;align-items:center;gap:12px;min-width:180px;flex:1">
    <div id="player-disc" style="
      width:44px;height:44px;border-radius:50%;flex-shrink:0;
      background:linear-gradient(135deg,var(--pink),var(--blue));
      display:flex;align-items:center;justify-content:center;
      font-size:1.2rem;
      animation:spin 4s linear infinite;
      animation-play-state:paused;
    ">🎵</div>
    <div style="min-width:0">
      <div id="player-title" style="font-weight:700;font-size:0.9rem;color:var(--dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px">
        Aucune chanson
      </div>
      <div id="player-artist" style="font-size:0.75rem;color:var(--muted)">—</div>
    </div>
  </div>

  <!-- Contrôles centraux -->
  <div style="display:flex;flex-direction:column;align-items:center;gap:6px;flex:2;max-width:500px">
    <div style="display:flex;align-items:center;gap:16px">
      <button onclick="playerPrev()" style="font-size:1.1rem;color:var(--muted);padding:4px;background:none;border:none;cursor:pointer;transition:all 0.2s" title="Précédent">⏮</button>
      <button id="btn-play" onclick="playerToggle()" style="
        width:44px;height:44px;border-radius:50%;border:none;cursor:pointer;
        background:linear-gradient(135deg,var(--pink),var(--blue));
        color:white;font-size:1.2rem;
        display:flex;align-items:center;justify-content:center;
        box-shadow:0 4px 15px rgba(255,45,120,0.4);
        transition:transform 0.2s;
      ">▶</button>
      <button onclick="playerNext()" style="font-size:1.1rem;color:var(--muted);padding:4px;background:none;border:none;cursor:pointer;transition:all 0.2s" title="Suivant">⏭</button>
    </div>

    <!-- Barre de progression -->
    <div style="display:flex;align-items:center;gap:8px;width:100%">
      <span id="player-current" style="font-size:0.7rem;color:var(--muted);min-width:32px;text-align:right">0:00</span>
      <div id="progress-wrap" onclick="playerSeek(event)" style="
        flex:1;height:5px;background:var(--pink-pale);border-radius:3px;
        cursor:pointer;position:relative;
      ">
        <div id="progress-fill" style="
          height:100%;width:0%;
          background:linear-gradient(90deg,var(--pink),var(--blue));
          border-radius:3px;pointer-events:none;
        "></div>
      </div>
      <span id="player-duration" style="font-size:0.7rem;color:var(--muted);min-width:32px">0:00</span>
    </div>
  </div>

  <!-- Volume + Fermer -->
  <div style="display:flex;align-items:center;gap:10px;flex:1;justify-content:flex-end">
    <span style="font-size:0.9rem">🔊</span>
    <input type="range" id="volume-slider" min="0" max="1" step="0.05" value="0.8"
      oninput="playerVolume(this.value)"
      style="width:80px;accent-color:var(--pink);cursor:pointer">
    <button onclick="playerClose()" style="
      background:none;border:none;font-size:1.1rem;color:var(--muted);
      cursor:pointer;padding:4px;margin-left:4px;
    " title="Fermer">✕</button>
  </div>
</div>

<!-- Élément audio caché -->
<audio id="audio-player" preload="none"></audio>

<!-- PAGE BAND -->
<div class="page-band">
  <h1>Explorer <span class="pink">la Musique</span> 🎵</h1>
  <p style="color:var(--muted);font-size:0.85rem;margin-top:6px">
    <?= count($chansons) ?> titres · Clique sur ▶ pour écouter
  </p>
</div>

<div class="section" style="padding-bottom:120px">

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

  <!-- Résultats recherche -->
  <div id="search-results"></div>

  <!-- Liste des chansons -->
  <div class="song-grid" id="song-list">
    <?php if (empty($chansons)): ?>
      <div class="empty-state"><p>Aucune chanson trouvée.</p></div>
    <?php else: ?>
      <?php foreach ($chansons as $i => $c):
        $fichier = mp3Path($c['titre']);
        $existe  = mp3Existe($c['titre']);
        $estFav  = $user ? estFavori($user['id'], $c['id']) : false;
      ?>
        <div class="song-card fade-in"
             style="animation-delay:<?= min($i*0.04,1.2) ?>s;cursor:pointer"
             data-genre="<?= htmlspecialchars($c['genre']) ?>"
             data-humeur="<?= htmlspecialchars($c['humeur']) ?>"
             data-src="<?= $existe ? htmlspecialchars($fichier) : '' ?>"
             data-titre="<?= htmlspecialchars($c['titre']) ?>"
             data-artiste="<?= htmlspecialchars($c['artiste']) ?>"
             data-index="<?= $i ?>">

          <div class="song-rank"><?= $i+1 ?></div>

          <!-- Bouton Play -->
          <button class="btn-play-song"
            style="
              width:44px;height:44px;border-radius:50%;flex-shrink:0;
              background:<?= $existe ? 'linear-gradient(135deg,var(--pink),var(--blue))' : '#eee' ?>;
              color:<?= $existe ? 'white' : 'var(--muted)' ?>;
              border:none;
              cursor:<?= $existe ? 'pointer' : 'not-allowed' ?>;
              font-size:1rem;
              display:flex;align-items:center;justify-content:center;
              box-shadow:<?= $existe ? '0 4px 12px rgba(255,45,120,0.3)' : 'none' ?>;
              transition:all 0.2s;
            "
            title="<?= $existe ? 'Écouter '.$c['titre'] : 'MP3 non trouvé : '.$fichier ?>"
            <?= !$existe ? 'disabled' : '' ?>>
            <?= $existe ? '▶' : '🚫' ?>
          </button>

          <div class="song-info">
            <div class="song-title"><?= htmlspecialchars($c['titre']) ?></div>
            <div class="song-artist"><?= htmlspecialchars($c['artiste']) ?></div>
          </div>

          <!-- Meta (vote, coeur) — stopPropagation pour ne pas trigger le play -->
          <div class="song-meta" onclick="event.stopPropagation()">
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

  <!-- Commentaires récents -->
  <?php if (!empty($chansons)): ?>
  <div style="margin-top:3rem">
    <div class="section-header-row">
      <div>
        <span class="section-label">Communauté</span>
        <h2 class="section-title">Commentaires <span class="pink">récents</span> 💬</h2>
      </div>
    </div>

    <?php
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
      <div class="empty-state"><p>Aucun commentaire encore. Sois le premier ! 💬</p></div>
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

    <?php if ($user): ?>
      <div style="margin-top:1rem;background:white;border:2px solid var(--border);border-radius:var(--radius-lg);padding:1.5rem">
        <form id="form-comment" data-chanson="<?= $chansons[0]['id'] ?>" style="display:flex;gap:10px">
          <input type="text" name="contenu" class="form-input" placeholder="Partage ton avis… 🎵" maxlength="300" required>
          <button type="submit" class="btn-pink" style="white-space:nowrap">Envoyer 💬</button>
        </form>
        <div id="comments-list" style="margin-top:10px"></div>
      </div>
    <?php else: ?>
      <div style="text-align:center;margin-top:1rem;padding:1.5rem;background:var(--pink-pale);border-radius:var(--radius-lg)">
        <p style="color:var(--pink);font-weight:600;margin-bottom:10px">Connecte-toi pour commenter ! 💬</p>
        <button class="btn-pink" data-open-auth="login">Se connecter</button>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>

<!-- Styles player -->
<style>
@keyframes spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
#btn-play:hover       { transform:scale(1.08) !important; }
.btn-play-song:hover:not(:disabled) { transform:scale(1.1) !important; }
.song-card.playing    { border-color:var(--pink) !important; background:var(--pink-pale) !important; }
.song-card.playing .song-title { color:var(--pink) !important; }
</style>

<!-- Script Player -->
<script>
(function() {
  'use strict';

  const audio      = document.getElementById('audio-player');
  const playerBar  = document.getElementById('player-bar');

  // Construire la playlist depuis les cards PHP
  const playlist = [];
  document.querySelectorAll('.song-card[data-src]').forEach(card => {
    if (card.dataset.src) {
      playlist.push({
        src:     card.dataset.src,
        titre:   card.dataset.titre,
        artiste: card.dataset.artiste,
        card:    card,
      });
    }
  });

  let current   = null;
  let playing   = false;

  // ─── Jouer une piste ───
  window.playSong = function(item) {
    // Réinitialiser l'ancienne carte
    if (current) {
      current.card.classList.remove('playing');
      const oldBtn = current.card.querySelector('.btn-play-song');
      if (oldBtn) oldBtn.textContent = '▶';
    }

    current    = item;
    audio.src  = item.src;
    audio.volume = parseFloat(document.getElementById('volume-slider').value);

    audio.play().then(() => {
      playing = true;
      refreshUI();
      item.card.classList.add('playing');
      const btn = item.card.querySelector('.btn-play-song');
      if (btn) btn.textContent = '⏸';
    }).catch(() => {
      if (typeof toast === 'function') toast('❌ Fichier MP3 introuvable !', 'error');
    });
  };

  // ─── Toggle play/pause ───
  window.playerToggle = function() {
    if (!current) return;
    if (playing) {
      audio.pause();
      playing = false;
      const btn = current.card.querySelector('.btn-play-song');
      if (btn) btn.textContent = '▶';
    } else {
      audio.play();
      playing = true;
      const btn = current.card.querySelector('.btn-play-song');
      if (btn) btn.textContent = '⏸';
    }
    refreshUI();
  };

  // ─── Suivant ───
  window.playerNext = function() {
    if (!playlist.length) return;
    const idx  = current ? playlist.findIndex(p => p.src === current.src) : -1;
    const next = playlist[(idx + 1) % playlist.length];
    playSong(next);
  };

  // ─── Précédent ───
  window.playerPrev = function() {
    if (!playlist.length) return;
    // Si plus de 3s de lecture → revenir au début
    if (audio.currentTime > 3) { audio.currentTime = 0; return; }
    const idx  = current ? playlist.findIndex(p => p.src === current.src) : 0;
    const prev = playlist[(idx - 1 + playlist.length) % playlist.length];
    playSong(prev);
  };

  // ─── Fermer ───
  window.playerClose = function() {
    audio.pause();
    audio.src = '';
    playing   = false;
    if (current) {
      current.card.classList.remove('playing');
      const btn = current.card.querySelector('.btn-play-song');
      if (btn) btn.textContent = '▶';
      current = null;
    }
    playerBar.style.display = 'none';
  };

  // ─── Volume ───
  window.playerVolume = function(val) {
    audio.volume = parseFloat(val);
  };

  // ─── Seek ───
  window.playerSeek = function(e) {
    if (!audio.duration) return;
    const wrap = document.getElementById('progress-wrap');
    const rect = wrap.getBoundingClientRect();
    const pct  = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
    audio.currentTime = pct * audio.duration;
  };

  // ─── Mise à jour UI ───
  function refreshUI() {
    if (!current) return;
    playerBar.style.display = 'flex';
    document.getElementById('player-title').textContent  = current.titre;
    document.getElementById('player-artist').textContent = current.artiste;
    document.getElementById('btn-play').textContent      = playing ? '⏸' : '▶';
    document.getElementById('player-disc').style.animationPlayState = playing ? 'running' : 'paused';
  }

  // ─── Progression ───
  audio.addEventListener('timeupdate', () => {
    if (!audio.duration) return;
    const pct = (audio.currentTime / audio.duration) * 100;
    document.getElementById('progress-fill').style.width = pct + '%';
    document.getElementById('player-current').textContent  = fmt(audio.currentTime);
    document.getElementById('player-duration').textContent = fmt(audio.duration);
  });

  // ─── Auto-suivant ───
  audio.addEventListener('ended', window.playerNext);

  function fmt(sec) {
    if (isNaN(sec)) return '0:00';
    const m = Math.floor(sec / 60);
    const s = Math.floor(sec % 60).toString().padStart(2, '0');
    return m + ':' + s;
  }

  // ─── Clic boutons ▶ sur les cartes ───
  document.querySelectorAll('.btn-play-song').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const card = this.closest('.song-card');
      const src  = card.dataset.src;
      if (!src) return;

      if (current && current.src === src) {
        playerToggle();
        return;
      }
      const item = playlist.find(p => p.src === src);
      if (item) {
        playSong(item);
      } else {
        // Chanson filtrée ou de recherche
        playSong({ src, titre: card.dataset.titre, artiste: card.dataset.artiste, card });
      }
    });
  });

  // ─── Clic sur la carte entière ───
  document.querySelectorAll('.song-card[data-src]').forEach(card => {
    card.addEventListener('click', function(e) {
      if (e.target.closest('.song-meta') || e.target.closest('.btn-play-song')) return;
      const src = this.dataset.src;
      if (!src) return;
      if (current && current.src === src) {
        playerToggle();
      } else {
        const item = playlist.find(p => p.src === src);
        if (item) playSong(item);
      }
    });
  });

})();
</script>

<?php require __DIR__ . '/php/footer.php'; ?>