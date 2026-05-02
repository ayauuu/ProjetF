<?php
$pageTitle = 'Mood du Jour';
require __DIR__ . '/php/header.php';

// Redirige si pas connecté
if (!$user) {
    // Affiche quand même la page mais avec CTA
}

$moodAujourdhui  = $user ? getMoodAujourdhui($user['id'])    : null;
$suggestions     = ($user && $moodAujourdhui) ? getSuggestionsMood($moodAujourdhui, $user['id']) : [];
$aVote           = $user ? aVoteAujourdhui($user['id']) : false;

$moods = [
  'happy'     => ['emoji'=>'😊','label'=>'Happy',     'color'=>'#FFD600','desc'=>'De bonne humeur, prêt à sourire !'],
  'chill'     => ['emoji'=>'😌','label'=>'Chill',      'color'=>'#00E5A0','desc'=>'Relax, on prend son temps…'],
  'energetic' => ['emoji'=>'⚡','label'=>'Energetic',  'color'=>'#FF2D78','desc'=>'Prêt à exploser ! L\'énergie à fond.'],
  'sad'       => ['emoji'=>'🥺','label'=>'Sad',        'color'=>'#7BA8FF','desc'=>'Un peu mélancolique aujourd\'hui…'],
  'romantic'  => ['emoji'=>'💕','label'=>'Romantic',   'color'=>'#FF7BAB','desc'=>'L\'amour est dans l\'air ✨'],
  'hype'      => ['emoji'=>'🔥','label'=>'Hype',       'color'=>'#FF6B2D','desc'=>'On est en mode fête totale !'],
];
?>

<div class="page-band">
  <h1>Mood <span class="pink">du Jour</span> 🎯</h1>
  <p style="color:var(--muted);font-size:0.85rem;margin-top:6px">
    Dis-nous comment tu te sens — on te trouve la musique parfaite
  </p>
</div>

<div class="section">
  <?php if (!$user): ?>
    <!-- CTA connexion -->
    <div style="text-align:center;padding:4rem 2rem;background:linear-gradient(135deg,var(--pink-pale),var(--blue-pale));border-radius:var(--radius-xl);margin-bottom:2rem">
      <div style="font-size:4rem;margin-bottom:1rem">🎯</div>
      <h2 style="font-family:var(--font-display);font-size:2rem;color:var(--dark);margin-bottom:0.5rem">
        Ton Mood, ta musique !
      </h2>
      <p style="color:var(--muted);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto">
        Connecte-toi pour sauvegarder ton humeur du jour et recevoir des suggestions musicales personnalisées.
      </p>
      <button class="btn-pink" data-open-auth="register">Rejoindre EnsiBeats 🎉</button>
    </div>
  <?php endif; ?>

  <div class="grid-2">
    <!-- MOOD SELECTOR -->
    <div>
      <span class="section-label">Étape 1</span>
      <h2 class="section-title">Comment tu te <span class="pink">sens</span> aujourd'hui ?</h2>

      <div class="mood-grid">
        <?php foreach ($moods as $key => $m): ?>
          <button class="mood-btn <?= $moodAujourdhui===$key?'active':'' ?>"
                  data-mood="<?= $key ?>"
                  <?= !$user ? 'onclick="document.querySelector(\'[data-open-auth=login]\').click()" ' : '' ?>
                  style="<?= $moodAujourdhui===$key?'border-color:'.$m['color'].';background:'.str_replace('#','rgba(',str_replace(')',')',str_replace(')',',0.1)',$m['color'])).':' : '' ?>">
            <span class="mood-emoji"><?= $m['emoji'] ?></span>
            <span class="mood-label"><?= $m['label'] ?></span>
          </button>
        <?php endforeach; ?>
      </div>

      <?php if ($moodAujourdhui && isset($moods[$moodAujourdhui])): ?>
        <div style="margin-top:1.25rem;padding:1rem 1.25rem;background:white;border:2px solid var(--border);border-radius:var(--radius);display:flex;align-items:center;gap:12px">
          <span style="font-size:1.8rem"><?= $moods[$moodAujourdhui]['emoji'] ?></span>
          <div>
            <div style="font-weight:700;color:var(--dark)">Ton mood d'aujourd'hui : <?= $moods[$moodAujourdhui]['label'] ?></div>
            <div style="font-size:0.8rem;color:var(--muted)"><?= $moods[$moodAujourdhui]['desc'] ?></div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- SUGGESTIONS -->
    <div>
      <span class="section-label">Étape 2</span>
      <h2 class="section-title">Suggestions <span class="blue">pour toi</span> 🎵</h2>

      <div id="mood-suggestions">
        <?php if (!empty($suggestions)): ?>
          <div class="suggest-grid">
            <?php foreach ($suggestions as $i => $s): ?>
              <?php $estFav = $user ? estFavori($user['id'], $s['id']) : false; ?>
              <div class="suggest-card pop-in" style="animation-delay:<?= $i*0.08 ?>s">
                <div class="emoji">🎵</div>
                <h4><?= htmlspecialchars($s['titre']) ?></h4>
                <p><?= htmlspecialchars($s['artiste']) ?></p>
                <span class="tag" style="margin:6px 0 10px"><?= genreLabel($s['genre']) ?></span>
                <?php if ($user): ?>
                  <div style="display:flex;gap:6px;justify-content:center;margin-top:4px">
                    <button class="btn-heart <?= $estFav?'active':'' ?>" data-id="<?= $s['id'] ?>">
                      <?= $estFav ? '❤️' : '🤍' ?>
                    </button>
                    <?php if (!$aVote): ?>
                      <button class="btn-vote" data-id="<?= $s['id'] ?>" style="font-size:0.68rem;padding:6px 12px">Voter</button>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php elseif ($user): ?>
          <div style="background:linear-gradient(135deg,var(--pink-pale),var(--blue-pale));border-radius:var(--radius-lg);padding:2.5rem;text-align:center">
            <div style="font-size:3rem;margin-bottom:1rem">🎯</div>
            <p style="color:var(--muted);font-weight:600">
              Choisis ton mood à gauche pour recevoir tes suggestions musicales !
            </p>
          </div>
        <?php else: ?>
          <div style="background:white;border:2px solid var(--border);border-radius:var(--radius-lg);padding:2rem;text-align:center">
            <div style="font-size:3rem;margin-bottom:1rem">🔒</div>
            <p style="color:var(--muted)">Connecte-toi pour voir tes suggestions</p>
            <button class="btn-pink" style="margin-top:1rem" data-open-auth="login">Se connecter</button>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- STATS DES MOODS DU CAMPUS -->
  <div style="margin-top:3rem">
    <span class="section-label">Le campus en chiffres</span>
    <h2 class="section-title">Mood <span class="pink">du Campus</span> 📊</h2>

    <?php
      $db = getDB();
      $today = date('Y-m-d');
      $moodStats = $db->prepare(
        "SELECT humeur, COUNT(*) as nb FROM moods WHERE date_mood = :d GROUP BY humeur ORDER BY nb DESC"
      );
      $moodStats->execute([':d' => $today]);
      $moodData = $moodStats->fetchAll();
      $totalMoods = array_sum(array_column($moodData, 'nb'));
    ?>

    <?php if (empty($moodData)): ?>
      <div class="empty-state">
        <p>Personne n'a encore partagé son mood aujourd'hui. Sois le premier ! 🎯</p>
      </div>
    <?php else: ?>
      <div style="background:white;border:2px solid var(--border);border-radius:var(--radius-lg);padding:1.5rem">
        <?php foreach ($moodData as $md): ?>
          <?php
            $pct = $totalMoods > 0 ? round($md['nb'] / $totalMoods * 100) : 0;
            $m   = $moods[$md['humeur']] ?? ['emoji'=>'🎵','label'=>$md['humeur'],'color'=>'#FF2D78'];
          ?>
          <div style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
              <span style="font-weight:700;font-size:0.88rem">
                <?= $m['emoji'] ?> <?= $m['label'] ?>
              </span>
              <span style="font-size:0.78rem;color:var(--muted)"><?= $md['nb'] ?> votes · <?= $pct ?>%</span>
            </div>
            <div style="background:var(--pink-pale);border-radius:4px;height:8px;overflow:hidden">
              <div style="width:<?= $pct ?>%;height:100%;background:linear-gradient(90deg,var(--pink),var(--blue));border-radius:4px;transition:width 1s ease"></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>

<?php require __DIR__ . '/php/footer.php'; ?>
