<?php
// ============================================
// EnsiBeats — API : Proposer une chanson
// ============================================

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/fonctions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée.']);
    exit;
}

// Lecture depuis JSON ou formulaire classique
$data = json_decode(file_get_contents('php://input'), true);

$titre   = trim($data['titre']   ?? $_POST['titre']   ?? '');
$artiste = trim($data['artiste'] ?? $_POST['artiste'] ?? '');
$genre   = trim($data['genre']   ?? $_POST['genre']   ?? 'autre');

if (empty($titre) || empty($artiste)) {
    http_response_code(400);
    echo json_encode(['error' => 'Titre et artiste obligatoires.']);
    exit;
}

$ip     = getIP();
$result = proposerChanson($titre, $artiste, $genre, $ip);

if (isset($result['error'])) {
    http_response_code(409);
}

echo json_encode($result);
?>
