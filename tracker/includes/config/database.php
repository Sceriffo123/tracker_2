<?php
// Determina se siamo in ambiente locale o produzione
$is_localhost = ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1');

if ($is_localhost) {
    // Configurazione Database Locale
    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'sql919391_2');
    define('DB_USER', 'root');         // Il tuo username locale MySQL
    define('DB_PASS', '');             // password vuota
} else {
    // Configurazione Database Aruba
    define('DB_HOST', '31.11.39.189');
    define('DB_NAME', 'Sql919391_2');
    define('DB_USER', 'Sql919391');
    define('DB_PASS', 'ot7375pv0y');
}

try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Errore connessione: " . $e->getMessage());
}
?>