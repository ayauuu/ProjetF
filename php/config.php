<?php
// ============================================
// EnsiBeats 2.0 — Configuration PDO
// ============================================

define('DB_HOST',    'localhost');
define('DB_NAME',    'ensibeats2');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');
define('SITE_NAME',  'EnsiBeats');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'DB Error: '.$e->getMessage()]));
        }
    }
    return $pdo;
}
?>
