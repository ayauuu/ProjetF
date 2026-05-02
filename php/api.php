<?php
// ============================================
// EnsiBeats 2.0 — API centrale (AJAX)
// ============================================

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../php/fonctions.php';

startSession();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$userId = $_SESSION['user_id'] ?? null;

// Actions nécessitant une connexion
$actionsProtegees = ['voter','favori','commenter','save_mood','update_profil'];
if (in_array($action, $actionsProtegees) && !$userId) {
    http_response_code(401);
    echo json_encode(['error' => 'Vous devez être connecté.']);
    exit;
}

switch ($action) {

    // ─── AUTH ───
    case 'register':
        $pseudo = $_POST['pseudo'] ?? '';
        $email  = $_POST['email']  ?? '';
        $mdp    = $_POST['mdp']    ?? '';
        echo json_encode(inscrire($pseudo, $email, $mdp));
        break;

    case 'login':
        $identifiant = $_POST['identifiant'] ?? '';
        $mdp         = $_POST['mdp']         ?? '';
        echo json_encode(connecter($identifiant, $mdp));
        break;

    case 'logout':
        deconnecter();
        echo json_encode(['success' => true]);
        break;

    // ─── VOTE ───
    case 'voter':
        $chansonId = (int)($_POST['chanson_id'] ?? 0);
        echo json_encode(voter($userId, $chansonId));
        break;

    // ─── FAVORIS ───
    case 'favori':
        $chansonId = (int)($_POST['chanson_id'] ?? 0);
        echo json_encode(toggleFavori($userId, $chansonId));
        break;

    // ─── COMMENTAIRE ───
    case 'commenter':
        $chansonId = (int)($_POST['chanson_id'] ?? 0);
        $contenu   = $_POST['contenu'] ?? '';
        echo json_encode(ajouterCommentaire($userId, $chansonId, $contenu));
        break;

    // ─── MOOD ───
    case 'save_mood':
        $humeur = $_POST['humeur'] ?? '';
        $result = saveMood($userId, $humeur);
        if (isset($result['success'])) {
            $suggestions = getSuggestionsMood($humeur, $userId);
            $result['suggestions'] = $suggestions;
        }
        echo json_encode($result);
        break;

    // ─── RECHERCHE ───
    case 'recherche':
        $q = $_GET['q'] ?? '';
        echo json_encode(rechercherChansons($q));
        break;

    // ─── COMMENTAIRES (chargement) ───
    case 'get_commentaires':
        $chansonId = (int)($_GET['chanson_id'] ?? 0);
        echo json_encode(getCommentaires($chansonId));
        break;

    // ─── PROFIL ───
    case 'update_profil':
        $bio   = $_POST['bio']          ?? '';
        $genre = $_POST['genre_favori'] ?? 'pop';
        echo json_encode(updateProfil($userId, $bio, $genre));
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Action inconnue.']);
}
?>
