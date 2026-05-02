<?php
// ============================================
// EnsiBeats — API : Recherche de chansons
// ============================================

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/fonctions.php';

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$resultats = rechercherChansons($query);
echo json_encode($resultats);
?>
