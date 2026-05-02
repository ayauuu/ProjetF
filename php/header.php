<?php
// ============================================
// EnsiBeats 2.0 — Header partiel
// ============================================
// Utilisation : require __DIR__ . '/php/header.php';
// Variables attendues : $pageTitle (optionnel)

require_once __DIR__ . '/fonctions.php';
startSession();

$user      = utilisateurConnecte();
$pageTitle = $pageTitle ?? 'EnsiBeats';
$initiales = $user ? initialesAvatar($user['pseudo']) : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> — EnsiBeats</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- NAV -->
<nav class="nav">
  <a class="nav-logo" href="index.php">EnsiBeats 🎵</a>

  <div class="nav-links">
    <a href="index.php">Accueil</a>
    <a href="explorer.php">Explorer</a>
    <a href="mood.php">Mood</a>
    <a href="leaderboard.php">Classement</a>
    <?php if ($user): ?>
      <a href="profil.php">Mon Profil</a>
    <?php endif; ?>
  </div>

  <div class="nav-right">
    <?php if ($user): ?>
      <div class="nav-user">
        <div class="avatar-sm" style="background:<?= htmlspecialchars($user['avatar_couleur']) ?>">
          <?= $initiales ?>
        </div>
        <span><?= htmlspecialchars($user['pseudo']) ?></span>
      </div>
      <button class="btn-ghost" data-logout>Déco</button>
    <?php else: ?>
      <button class="btn-nav-auth" data-open-auth="login">Connexion</button>
    <?php endif; ?>
  </div>
</nav>

<!-- AUTH MODAL -->
<div class="modal-overlay" id="auth-modal">
  <div class="modal">
    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:1.25rem">
      <div>
        <div class="modal-title">EnsiBeats 🎵</div>
        <div class="modal-sub">La musique de l'ENSI Tunis</div>
      </div>
      <button data-close-modal style="font-size:1.4rem;color:var(--muted);padding:4px">✕</button>
    </div>

    <div class="modal-tabs">
      <div class="modal-tab active" data-tab="login">Connexion</div>
      <div class="modal-tab" data-tab="register">Inscription</div>
    </div>

    <!-- LOGIN -->
    <form class="auth-form active" id="form-login">
      <div class="form-group">
        <label class="form-label">Pseudo ou Email</label>
        <input type="text" name="identifiant" class="form-input" placeholder="Ton pseudo ou email…" required autocomplete="username">
      </div>
      <div class="form-group">
        <label class="form-label">Mot de passe</label>
        <input type="password" name="mdp" class="form-input" placeholder="••••••" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn-pink" style="width:100%;margin-top:0.5rem">Se connecter</button>
      <p style="text-align:center;margin-top:1rem;font-size:0.8rem;color:var(--muted)">
        Pas encore de compte ?
        <a href="#" style="color:var(--pink);font-weight:700" onclick="document.querySelector('[data-tab=register]').click();return false">Créer un compte</a>
      </p>
    </form>

    <!-- REGISTER -->
    <form class="auth-form" id="form-register">
      <div class="form-group">
        <label class="form-label">Pseudo</label>
        <input type="text" name="pseudo" class="form-input" placeholder="Choisis un pseudo cool…" required minlength="3" maxlength="50" autocomplete="username">
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-input" placeholder="ton@email.com" required autocomplete="email">
      </div>
      <div class="form-group">
        <label class="form-label">Mot de passe</label>
        <input type="password" name="mdp" class="form-input" placeholder="Min 6 caractères" required minlength="6" autocomplete="new-password">
      </div>
      <button type="submit" class="btn-pink" style="width:100%;margin-top:0.5rem">Créer mon compte 🎉</button>
    </form>
  </div>
</div>
