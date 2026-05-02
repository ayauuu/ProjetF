<?php
// ============================================
// EnsiBeats 2.0 — Fonctions principales
// ============================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// ─── CHANSONS ───

function getChansons(int $limit = 20, string $genre = '', string $humeur = ''): array {
    $db  = getDB();
    $sql = "SELECT * FROM chansons WHERE 1=1";
    $p   = [];
    if ($genre  && $genre  !== 'tous') { $sql .= " AND genre = :genre";   $p[':genre']  = $genre; }
    if ($humeur && $humeur !== 'tous') { $sql .= " AND humeur = :humeur"; $p[':humeur'] = $humeur; }
    $sql .= " ORDER BY votes_total DESC LIMIT :limit";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    foreach ($p as $k => $v) $stmt->bindValue($k, $v);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getChanson(int $id): ?array {
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM chansons WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function rechercherChansons(string $q): array {
    $db   = getDB();
    $like = '%'.trim($q).'%';
    $stmt = $db->prepare(
        "SELECT * FROM chansons WHERE titre LIKE :q OR artiste LIKE :q ORDER BY votes_total DESC LIMIT 20"
    );
    $stmt->execute([':q' => $like]);
    return $stmt->fetchAll();
}

function getSuggestionsMood(string $humeur, int $userId): array {
    $db   = getDB();
    $stmt = $db->prepare(
        "SELECT c.*, IF(f.id IS NOT NULL, 1, 0) as est_favori
         FROM chansons c
         LEFT JOIN favoris f ON f.chanson_id = c.id AND f.utilisateur_id = :uid
         WHERE c.humeur = :humeur
         ORDER BY c.votes_total DESC LIMIT 6"
    );
    $stmt->execute([':humeur' => $humeur, ':uid' => $userId]);
    return $stmt->fetchAll();
}

// ─── FAVORIS ───

function getFavoris(int $userId): array {
    $db   = getDB();
    $stmt = $db->prepare(
        "SELECT c.*, f.date_ajout as date_favori
         FROM favoris f
         JOIN chansons c ON c.id = f.chanson_id
         WHERE f.utilisateur_id = :uid
         ORDER BY f.date_ajout DESC"
    );
    $stmt->execute([':uid' => $userId]);
    return $stmt->fetchAll();
}

function toggleFavori(int $userId, int $chansonId): array {
    $db   = getDB();
    $stmt = $db->prepare("SELECT id FROM favoris WHERE utilisateur_id = :u AND chanson_id = :c");
    $stmt->execute([':u' => $userId, ':c' => $chansonId]);
    $existe = $stmt->fetch();

    if ($existe) {
        $db->prepare("DELETE FROM favoris WHERE id = :id")->execute([':id' => $existe['id']]);
        $db->prepare("UPDATE chansons SET nb_favoris = GREATEST(nb_favoris-1,0) WHERE id=:id")->execute([':id'=>$chansonId]);
        // -1 point
        $db->prepare("UPDATE utilisateurs SET points = GREATEST(points-1,0) WHERE id=:id")->execute([':id'=>$userId]);
        return ['action' => 'removed'];
    } else {
        $db->prepare("INSERT INTO favoris (utilisateur_id, chanson_id) VALUES (:u,:c)")->execute([':u'=>$userId,':c'=>$chansonId]);
        $db->prepare("UPDATE chansons SET nb_favoris = nb_favoris+1 WHERE id=:id")->execute([':id'=>$chansonId]);
        // +2 points
        $db->prepare("UPDATE utilisateurs SET points = points+2 WHERE id=:id")->execute([':id'=>$userId]);
        return ['action' => 'added'];
    }
}

function estFavori(int $userId, int $chansonId): bool {
    $db   = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM favoris WHERE utilisateur_id=:u AND chanson_id=:c");
    $stmt->execute([':u'=>$userId,':c'=>$chansonId]);
    return (int)$stmt->fetchColumn() > 0;
}

// ─── COMMENTAIRES ───

function getCommentaires(int $chansonId): array {
    $db   = getDB();
    $stmt = $db->prepare(
        "SELECT cm.*, u.pseudo, u.avatar_couleur
         FROM commentaires cm
         JOIN utilisateurs u ON u.id = cm.utilisateur_id
         WHERE cm.chanson_id = :cid
         ORDER BY cm.date_commentaire DESC LIMIT 30"
    );
    $stmt->execute([':cid' => $chansonId]);
    return $stmt->fetchAll();
}

function ajouterCommentaire(int $userId, int $chansonId, string $contenu): array {
    $contenu = trim(htmlspecialchars($contenu));
    if (strlen($contenu) < 1) return ['error' => 'Commentaire vide.'];
    if (strlen($contenu) > 300) return ['error' => 'Trop long (max 300 caractères).'];

    $db = getDB();
    $db->prepare(
        "INSERT INTO commentaires (utilisateur_id, chanson_id, contenu) VALUES (:u,:c,:txt)"
    )->execute([':u'=>$userId,':c'=>$chansonId,':txt'=>$contenu]);

    $db->prepare("UPDATE chansons SET nb_commentaires=nb_commentaires+1 WHERE id=:id")
       ->execute([':id'=>$chansonId]);
    // +3 points
    $db->prepare("UPDATE utilisateurs SET points=points+3 WHERE id=:id")->execute([':id'=>$userId]);

    $stmt = $db->prepare("SELECT u.pseudo, u.avatar_couleur FROM utilisateurs u WHERE u.id=:id");
    $stmt->execute([':id'=>$userId]);
    $u = $stmt->fetch();

    return ['success'=>true, 'pseudo'=>$u['pseudo'], 'avatar_couleur'=>$u['avatar_couleur'], 'contenu'=>$contenu];
}

// ─── VOTES ───

function voter(int $userId, int $chansonId): array {
    $db    = getDB();
    $today = date('Y-m-d');
    $stmt  = $db->prepare("SELECT COUNT(*) FROM votes_jour WHERE utilisateur_id=:u AND date_vote=:d");
    $stmt->execute([':u'=>$userId,':d'=>$today]);
    if ((int)$stmt->fetchColumn() > 0) return ['error'=>'Vous avez déjà voté aujourd\'hui !'];

    try {
        $db->beginTransaction();
        $db->prepare("INSERT INTO votes_jour (utilisateur_id,chanson_id,date_vote) VALUES(:u,:c,:d)")
           ->execute([':u'=>$userId,':c'=>$chansonId,':d'=>$today]);
        $db->prepare("UPDATE chansons SET votes_total=votes_total+1 WHERE id=:id")
           ->execute([':id'=>$chansonId]);
        // +5 points pour voter
        $db->prepare("UPDATE utilisateurs SET points=points+5 WHERE id=:id")
           ->execute([':id'=>$userId]);
        $db->commit();
        return ['success'=>true];
    } catch (PDOException $e) {
        $db->rollBack();
        if ($e->getCode()==='23000') return ['error'=>'Déjà voté !'];
        return ['error'=>$e->getMessage()];
    }
}

function aVoteAujourdhui(int $userId): bool {
    $db   = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM votes_jour WHERE utilisateur_id=:u AND date_vote=:d");
    $stmt->execute([':u'=>$userId,':d'=>date('Y-m-d')]);
    return (int)$stmt->fetchColumn() > 0;
}

// ─── MOOD ───

function saveMood(int $userId, string $humeur): array {
    $valides = ['happy','chill','energetic','sad','romantic','hype'];
    if (!in_array($humeur, $valides)) return ['error'=>'Humeur invalide.'];

    $db    = getDB();
    $today = date('Y-m-d');
    $stmt  = $db->prepare(
        "INSERT INTO moods (utilisateur_id,humeur,date_mood) VALUES(:u,:h,:d)
         ON DUPLICATE KEY UPDATE humeur=:h2"
    );
    $stmt->execute([':u'=>$userId,':h'=>$humeur,':d'=>$today,':h2'=>$humeur]);
    return ['success'=>true];
}

function getMoodAujourdhui(int $userId): ?string {
    $db   = getDB();
    $stmt = $db->prepare("SELECT humeur FROM moods WHERE utilisateur_id=:u AND date_mood=:d");
    $stmt->execute([':u'=>$userId,':d'=>date('Y-m-d')]);
    $r = $stmt->fetch();
    return $r ? $r['humeur'] : null;
}

// ─── LEADERBOARD ───

function getLeaderboard(int $limit = 10): array {
    $db   = getDB();
    $stmt = $db->prepare(
        "SELECT id, pseudo, avatar_couleur, points, genre_favori,
                (SELECT COUNT(*) FROM favoris WHERE utilisateur_id=u.id) as nb_favoris,
                (SELECT COUNT(*) FROM commentaires WHERE utilisateur_id=u.id) as nb_comments
         FROM utilisateurs u
         ORDER BY points DESC LIMIT :limit"
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// ─── PROFIL ───

function updateProfil(int $userId, string $bio, string $genreFavori): array {
    $bio = trim(htmlspecialchars($bio));
    $genres = ['pop','rnb','electro','rock','rap','jazz','autre'];
    if (!in_array($genreFavori, $genres)) $genreFavori = 'pop';
    if (strlen($bio) > 200) return ['error' => 'Bio trop longue (max 200 caractères).'];

    $db = getDB();
    $db->prepare("UPDATE utilisateurs SET bio=:bio, genre_favori=:g WHERE id=:id")
       ->execute([':bio'=>$bio,':g'=>$genreFavori,':id'=>$userId]);
    return ['success'=>true];
}

// ─── HELPERS ───

function genreLabel(string $g): string {
    return match($g) {
        'pop'=>'Pop','rnb'=>'R&B','electro'=>'Électro',
        'rock'=>'Rock','rap'=>'Rap','jazz'=>'Jazz',default=>'Autre'
    };
}

function humeurLabel(string $h): string {
    return match($h) {
        'happy'=>'Happy 😊','chill'=>'Chill 😌','energetic'=>'Energetic ⚡',
        'sad'=>'Sad 🥺','romantic'=>'Romantic 💕','hype'=>'Hype 🔥',default=>'?'
    };
}

function humeurEmoji(string $h): string {
    return match($h) {
        'happy'=>'😊','chill'=>'😌','energetic'=>'⚡','sad'=>'🥺','romantic'=>'💕','hype'=>'🔥',default=>'🎵'
    };
}

function initialesAvatar(string $pseudo): string {
    return strtoupper(mb_substr($pseudo, 0, 2));
}
?>
