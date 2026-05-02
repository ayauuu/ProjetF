<?php
// ============================================
// EnsiBeats — Admin : Gestion des propositions
// Accès : /admin/index.php
// À PROTÉGER par un mot de passe en production !
// ============================================
require_once __DIR__ . '/../php/config.php';

// Protection basique par mot de passe (personnalisez)
$ADMIN_PASS = 'ensibeats2026';

session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $ADMIN_PASS) {
        $_SESSION['admin'] = true;
    } else {
        $error = 'Mot de passe incorrect.';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$loggedIn = !empty($_SESSION['admin']);

// Actions admin
if ($loggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();

    // Approuver une proposition → l'ajouter dans chansons
    if (isset($_POST['approuver'])) {
        $id = (int)$_POST['prop_id'];
        $stmt = $db->prepare("SELECT * FROM propositions WHERE id = :id AND statut = 'en_attente'");
        $stmt->execute([':id' => $id]);
        $prop = $stmt->fetch();

        if ($prop) {
            $db->beginTransaction();
            // Insérer dans chansons
            $ins = $db->prepare("INSERT INTO chansons (titre, artiste, genre) VALUES (:t, :a, :g)");
            $ins->execute([':t' => $prop['titre'], ':a' => $prop['artiste'], ':g' => $prop['genre']]);
            // Marquer comme approuvé
            $upd = $db->prepare("UPDATE propositions SET statut = 'approuve' WHERE id = :id");
            $upd->execute([':id' => $id]);
            $db->commit();
        }
    }

    // Refuser une proposition
    if (isset($_POST['refuser'])) {
        $id = (int)$_POST['prop_id'];
        $stmt = $db->prepare("UPDATE propositions SET statut = 'refuse' WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    // Supprimer une chanson
    if (isset($_POST['supprimer_chanson'])) {
        $id = (int)$_POST['chanson_id'];
        $stmt = $db->prepare("DELETE FROM chansons WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}

// Charger les données
if ($loggedIn) {
    $db = getDB();

    $props = $db->query(
        "SELECT * FROM propositions ORDER BY statut = 'en_attente' DESC, date_proposition DESC LIMIT 50"
    )->fetchAll();

    $chansons = $db->query(
        "SELECT * FROM chansons ORDER BY votes_total DESC LIMIT 30"
    )->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — EnsiBeats</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px 14px; text-align: left; border-bottom: 1px solid var(--border); font-size: 0.83rem; }
    th { font-size: 0.65rem; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted); }
    tr:hover td { background: var(--surface2); }
    .badge-attente  { color: var(--gold); }
    .badge-approuve { color: #6dbd6d; }
    .badge-refuse   { color: var(--red); }
    .action-btn {
      font-size: 0.68rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;
      padding: 5px 12px; border-radius: 4px; border: 1px solid; cursor: pointer; transition: all 0.2s;
      background: none; font-family: var(--font-head);
    }
    .btn-approve { border-color: #6dbd6d; color: #6dbd6d; }
    .btn-approve:hover { background: #6dbd6d; color: var(--black); }
    .btn-refuse  { border-color: var(--red); color: var(--red); }
    .btn-refuse:hover  { background: var(--red); color: #fff; }
    .btn-delete  { border-color: var(--muted); color: var(--muted); }
    .btn-delete:hover  { background: var(--muted); color: var(--black); }
    .login-wrap { max-width: 360px; margin: 8rem auto; }
    .tabs { display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 1px solid var(--border); }
    .tab { font-size: 0.75rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; padding: 10px 0; color: var(--muted); cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.2s; }
    .tab.active, .tab:hover { color: var(--gold); border-bottom-color: var(--gold); }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
  </style>
</head>
<body>

<nav class="nav">
  <a class="nav-logo" href="../index.php">ENSI<span>BEATS</span></a>
  <div class="nav-links">
    <a href="../index.php">← Retour au site</a>
    <?php if ($loggedIn): ?>
      <a href="?logout=1" style="color:var(--red)">Déconnexion</a>
    <?php endif; ?>
  </div>
  <div class="nav-live"><span class="live-dot"></span> Admin</div>
</nav>

<div class="section">

<?php if (!$loggedIn): ?>

  <div class="login-wrap">
    <div class="section-header">
      <div>
        <p class="section-title">Espace privé</p>
        <h2 class="section-headline">Admin <em>Panel</em></h2>
      </div>
    </div>

    <?php if ($error): ?>
      <p style="color:var(--red);margin-bottom:1rem;font-size:0.85rem"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
      <input type="password" name="password" class="form-input" placeholder="Mot de passe admin…" autofocus required style="margin-bottom:12px">
      <button type="submit" class="btn-primary">Connexion</button>
    </form>
  </div>

<?php else: ?>

  <div class="section-header">
    <div>
      <p class="section-title">Backoffice</p>
      <h2 class="section-headline">Admin <em>Panel</em></h2>
    </div>
  </div>

  <div class="tabs">
    <div class="tab active" data-tab="propositions">
      Propositions
      <?php $enAttente = count(array_filter($props, fn($p) => $p['statut'] === 'en_attente')); ?>
      <?php if ($enAttente): ?><span class="text-gold"> (<?= $enAttente ?>)</span><?php endif; ?>
    </div>
    <div class="tab" data-tab="chansons">Gérer les chansons</div>
  </div>

  <!-- Tab : Propositions -->
  <div class="tab-content active" id="tab-propositions">
    <table>
      <thead>
        <tr><th>Titre</th><th>Artiste</th><th>Genre</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($props as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['titre']) ?></td>
          <td><?= htmlspecialchars($p['artiste']) ?></td>
          <td><?= htmlspecialchars($p['genre']) ?></td>
          <td class="badge-<?= $p['statut'] ?>"><?= $p['statut'] ?></td>
          <td><?= date('d/m/y H:i', strtotime($p['date_proposition'])) ?></td>
          <td>
            <?php if ($p['statut'] === 'en_attente'): ?>
            <form method="POST" style="display:inline">
              <input type="hidden" name="prop_id" value="<?= $p['id'] ?>">
              <button name="approuver" class="action-btn btn-approve">✓ Approuver</button>
            </form>
            <form method="POST" style="display:inline">
              <input type="hidden" name="prop_id" value="<?= $p['id'] ?>">
              <button name="refuser" class="action-btn btn-refuse">✕ Refuser</button>
            </form>
            <?php else: ?>
              <span style="font-size:0.75rem;color:var(--muted)">Traité</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($props)): ?>
          <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:2rem">Aucune proposition.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Tab : Gérer les chansons -->
  <div class="tab-content" id="tab-chansons">
    <table>
      <thead>
        <tr><th>#</th><th>Titre</th><th>Artiste</th><th>Genre</th><th>Votes</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php foreach ($chansons as $i => $c): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= htmlspecialchars($c['titre']) ?></td>
          <td><?= htmlspecialchars($c['artiste']) ?></td>
          <td><?= htmlspecialchars($c['genre']) ?></td>
          <td class="text-gold"><?= number_format($c['votes_total']) ?></td>
          <td>
            <form method="POST" onsubmit="return confirm('Supprimer cette chanson ?')">
              <input type="hidden" name="chanson_id" value="<?= $c['id'] ?>">
              <button name="supprimer_chanson" class="action-btn btn-delete">Supprimer</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php endif; ?>
</div>

<footer>
  <div class="logo">ENSIBEATS</div>
  <p>Admin Panel · EnsiBeats</p>
</footer>

<script>
document.querySelectorAll('.tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    tab.classList.add('active');
    document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
  });
});
</script>
</body>
</html>
