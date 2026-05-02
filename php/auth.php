<?php
// ============================================
// EnsiBeats 2.0 — Authentification
// ============================================

require_once __DIR__ . '/config.php';

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function estConnecte(): bool {
    startSession();
    return !empty($_SESSION['user_id']);
}

function utilisateurConnecte(): ?array {
    if (!estConnecte()) return null;
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function inscrire(string $pseudo, string $email, string $mdp): array {
    $pseudo = trim(htmlspecialchars($pseudo));
    $email  = trim(filter_var($email, FILTER_SANITIZE_EMAIL));

    if (strlen($pseudo) < 3 || strlen($pseudo) > 50) {
        return ['error' => 'Le pseudo doit faire entre 3 et 50 caractères.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['error' => 'Email invalide.'];
    }
    if (strlen($mdp) < 6) {
        return ['error' => 'Mot de passe trop court (min 6 caractères).'];
    }

    $db   = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM utilisateurs WHERE pseudo = :p OR email = :e");
    $stmt->execute([':p' => $pseudo, ':e' => $email]);
    if ((int)$stmt->fetchColumn() > 0) {
        return ['error' => 'Pseudo ou email déjà utilisé.'];
    }

    $hash = password_hash($mdp, PASSWORD_BCRYPT);
    $couleurs = ['#FF2D78','#2D6AFF','#FF6B2D','#2DFFB4','#FF2DE8','#FFD600'];
    $couleur  = $couleurs[array_rand($couleurs)];

    $stmt = $db->prepare(
        "INSERT INTO utilisateurs (pseudo, email, mot_de_passe, avatar_couleur)
         VALUES (:pseudo, :email, :mdp, :couleur)"
    );
    $stmt->execute([
        ':pseudo'  => $pseudo,
        ':email'   => $email,
        ':mdp'     => $hash,
        ':couleur' => $couleur,
    ]);

    $id = (int)$db->lastInsertId();
    startSession();
    $_SESSION['user_id'] = $id;
    return ['success' => true, 'pseudo' => $pseudo];
}

function connecter(string $identifiant, string $mdp): array {
    $identifiant = trim($identifiant);
    $db   = getDB();
    $stmt = $db->prepare(
        "SELECT * FROM utilisateurs WHERE pseudo = :i OR email = :i LIMIT 1"
    );
    $stmt->execute([':i' => $identifiant]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($mdp, $user['mot_de_passe'])) {
        return ['error' => 'Identifiant ou mot de passe incorrect.'];
    }

    startSession();
    $_SESSION['user_id'] = $user['id'];
    return ['success' => true, 'pseudo' => $user['pseudo']];
}

function deconnecter(): void {
    startSession();
    session_destroy();
}
?>
