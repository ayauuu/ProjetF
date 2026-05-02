<?php
// ============================================
// EnsiBeats — API : Traitement du vote
// ============================================

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/fonctions.php';

// Accepte seulement POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée.']);
    exit;
}

// Lire le body JSON ou les données POST
$data = json_decode(file_get_contents('php://input'), true);

$chansonId = isset($data['chanson_id'])
    ? (int)$data['chanson_id']
    : (isset($_POST['chanson_id']) ? (int)$_POST['chanson_id'] : 0);

if ($chansonId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de chanson invalide.']);
    exit;
}

$ip     = getIP();
$result = voter($chansonId, $ip);

if (isset($result['error'])) {
    http_response_code(409);
} else {
    // Retourner aussi le nb de votes actuels
    $votes = getVotesAujourdhui();
    $result['votes_jour'] = $votes;
}

echo json_encode($result);
?>
