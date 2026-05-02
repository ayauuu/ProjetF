# 🎵 EnsiBeats — Guide d'installation

## Structure du projet

```
ensibeats/
├── index.php          ← Page d'accueil
├── charts.php         ← Top Charts avec recherche + filtres
├── pole.php           ← Vote du jour + Propositions
├── discover.php       ← Découvrir tous les genres
├── database.sql       ← Script SQL à importer
│
├── css/
│   └── style.css      ← Design system complet
│
├── js/
│   └── main.js        ← JavaScript interactif
│
├── php/
│   ├── config.php     ← Connexion PDO (à configurer)
│   ├── fonctions.php  ← Toutes les fonctions DB
│   ├── voter.php      ← API POST : voter
│   ├── proposer.php   ← API POST : proposer une chanson
│   └── recherche.php  ← API GET : recherche
│
└── admin/
    └── index.php      ← Panel admin (propositions + gestion)
```

## 🚀 Installation

### 1. Prérequis
- PHP 8.0+
- MySQL 5.7+ ou MariaDB 10.4+
- Serveur web : XAMPP, WAMP, Laragon (Windows) ou Apache/Nginx

### 2. Base de données
```sql
-- Dans phpMyAdmin ou MySQL CLI :
SOURCE database.sql;
```
Ou ouvrir phpMyAdmin → Importer → Choisir `database.sql`

### 3. Configuration
Modifier `php/config.php` :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ensibeats');
define('DB_USER', 'root');       // votre utilisateur MySQL
define('DB_PASS', '');           // votre mot de passe MySQL
```

### 4. Lancer le projet
- Copier le dossier dans `htdocs/` (XAMPP) ou `www/` (WAMP)
- Ouvrir : `http://localhost/ensibeats/`

---

## 🔐 Panel Admin
- URL : `http://localhost/ensibeats/admin/`
- Mot de passe par défaut : `ensibeats2026`
- Changer dans `admin/index.php` ligne `$ADMIN_PASS`

### Fonctionnalités Admin :
- ✅ Approuver/refuser les propositions de chansons
- 🗑️ Supprimer des chansons du catalogue

---

## 📋 Fonctionnalités couvertes (selon le livrable)

### JavaScript (côté client)
- ✅ Champs formulaires HTML5 (validation `required`, `maxlength`)
- ✅ Contrôles de saisie avant envoi (validation JS avant POST)
- ✅ Fonctions utiles : `showToast()`, `initVoting()`, `initSearch()`, `initFilters()`, `initProgressBars()`
- ✅ Recherche dynamique avec debounce
- ✅ Filtres par genre sans rechargement de page

### PHP (côté serveur)
- ✅ Extension **PDO** utilisée partout (`config.php`)
- ✅ Connexion MySQL via PDO avec `PDO::ATTR_ERRMODE_EXCEPTION`
- ✅ **INSERT** : voter, proposer une chanson, admin approuve → insère dans `chansons`
- ✅ **SELECT** : getTopCharts, getChansonsVote, getPalmares, recherche
- ✅ **UPDATE** : incrémenter votes_total, changer statut proposition
- ✅ **DELETE** : admin peut supprimer une chanson
- ✅ Pages de **modification/suppression** dans l'admin
- ✅ Requêtes préparées (protection injection SQL)
- ✅ Transactions PDO (vote atomique)

---

## 🎨 Design
- Police display : **Bebas Neue**
- Police titraille : **Syne**
- Police corps : **DM Sans**
- Thème : Dark luxury × Music energy
- Couleurs : Noir profond + Or + Rouge carmin
