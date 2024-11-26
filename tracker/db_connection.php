<?php
// Abilitazione della gestione degli errori
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Parametri di connessione al database
$host = '127.0.0.1';
$dbname = 'sql919391_2';
$username = 'root';
$password = '';

// Creazione della connessione tramite PDO
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    echo "✅ Connessione al database riuscita!";
} catch (PDOException $e) {
    echo "❌ Errore di connessione al database: " . $e->getMessage();
}
?>
