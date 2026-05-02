<?php
// ============================================
// EnsiBeats 2.0 — Mapping chansons → fichiers MP3
// Dossier : /song/
// ============================================

/**
 * Convertit un titre en nom de fichier MP3
 * Ex: "Shape of You" → "shape-of-you.mp3"
 */
function titreToFichier(string $titre): string {
    $titre = strtolower($titre);
    $titre = str_replace(
        ['à','â','ä','é','è','ê','ë','î','ï','ô','ö','ù','û','ü','ç','ñ',"'",' '],
        ['a','a','a','e','e','e','e','i','i','o','o','u','u','u','c','n','-','-'],
        $titre
    );
    // Supprimer les caractères spéciaux sauf tiret
    $titre = preg_replace('/[^a-z0-9\-]/', '', $titre);
    // Supprimer les tirets multiples
    $titre = preg_replace('/-+/', '-', $titre);
    $titre = trim($titre, '-');
    return $titre . '.mp3';
}

/**
 * Vérifie si le fichier MP3 existe dans /song/
 */
function mp3Existe(string $titre): bool {
    $fichier = titreToFichier($titre);
    return file_exists(__DIR__ . '/../song/' . $fichier);
}

/**
 * Retourne le chemin web du MP3 (relatif à la racine du site)
 */
function mp3Path(string $titre): string {
    return 'song/' . titreToFichier($titre);
}
?>
