-- ============================================
-- EnsiBeats 2.0 — Base de données complète
-- ============================================

CREATE DATABASE IF NOT EXISTS ensibeats2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ensibeats2;

-- ─── UTILISATEURS ───
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    avatar_couleur VARCHAR(7) DEFAULT '#FF2D78',
    genre_favori ENUM('pop','rnb','electro','rock','rap','jazz','autre') DEFAULT 'pop',
    bio VARCHAR(200) DEFAULT '',
    points INT DEFAULT 0,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ─── CHANSONS ───
CREATE TABLE IF NOT EXISTS chansons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    artiste VARCHAR(255) NOT NULL,
    genre ENUM('pop','rnb','electro','rock','rap','jazz','autre') NOT NULL DEFAULT 'autre',
    humeur ENUM('happy','chill','energetic','sad','romantic','hype') DEFAULT 'happy',
    votes_total INT DEFAULT 0,
    nb_favoris INT DEFAULT 0,
    nb_commentaires INT DEFAULT 0,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ─── FAVORIS ───
CREATE TABLE IF NOT EXISTS favoris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    chanson_id INT NOT NULL,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (chanson_id) REFERENCES chansons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favori (utilisateur_id, chanson_id)
);

-- ─── COMMENTAIRES ───
CREATE TABLE IF NOT EXISTS commentaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    chanson_id INT NOT NULL,
    contenu VARCHAR(300) NOT NULL,
    date_commentaire TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (chanson_id) REFERENCES chansons(id) ON DELETE CASCADE
);

-- ─── VOTES DU JOUR ───
CREATE TABLE IF NOT EXISTS votes_jour (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    chanson_id INT NOT NULL,
    date_vote DATE NOT NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (chanson_id) REFERENCES chansons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (utilisateur_id, date_vote)
);

-- ─── MOOD DU JOUR ───
CREATE TABLE IF NOT EXISTS moods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    humeur ENUM('happy','chill','energetic','sad','romantic','hype') NOT NULL,
    date_mood DATE NOT NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_mood (utilisateur_id, date_mood)
);

-- ─── DONNÉES DE DÉPART ───
INSERT INTO chansons (titre, artiste, genre, humeur, votes_total, nb_favoris) VALUES
('Shape of You',      'Ed Sheeran',    'pop',    'happy',    1250, 89),
('Blinding Lights',   'The Weeknd',    'rnb',    'energetic',1100, 76),
('Levitating',        'Dua Lipa',      'pop',    'hype',      950, 65),
('Stay',              'Justin Bieber', 'pop',    'romantic',  820, 54),
('Peaches',           'Justin Bieber', 'rnb',    'chill',     700, 48),
('One More Time',     'Daft Punk',     'electro','hype',      670, 72),
('Bohemian Rhapsody', 'Queen',         'rock',   'energetic', 610, 91),
('Flowers',           'Miley Cyrus',   'pop',    'happy',     580, 43),
('Anti-Hero',         'Taylor Swift',  'pop',    'chill',     540, 67),
('As It Was',         'Harry Styles',  'pop',    'sad',       490, 38),
('Heat Waves',        'Glass Animals', 'electro','chill',     460, 55),
('Starboy',           'The Weeknd',    'rnb',    'hype',      430, 41),
('Bad Guy',           'Billie Eilish', 'pop',    'energetic', 410, 60),
('Golden Hour',       'JVKE',          'pop',    'romantic',  390, 44),
('Calm Down',         'Rema',          'pop',    'happy',     370, 35),
('Ella Baila Sola',   'Eslabon Armado','pop',    'romantic',  350, 29),
('Cupid',             'FIFTY FIFTY',   'pop',    'happy',     330, 32),
('Escapism',          'RAYE',          'rnb',    'chill',     310, 27),
('Lose Control',      'Teddy Swims',   'rnb',    'sad',       290, 31),
('Cruel Summer',      'Taylor Swift',  'pop',    'hype',      270, 58);
