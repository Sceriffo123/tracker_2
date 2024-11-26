<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Parametri di connessione al database
$host = '127.0.0.1';
$dbname = 'sql919391_2';
$username = 'root';
$password = '';

try {
    // Tentativo di connessione
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<div style='color: green; font-family: Arial; padding: 20px;'>";
    echo "✅ Connessione al database riuscita!<br>";
    
    // Test query sulla tabella avvistamenti
    $stmt = $conn->query("SHOW TABLES LIKE 'avvistamenti'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabella 'avvistamenti' trovata!<br>";
        
        // Verifica struttura tabella
        $stmt = $conn->query("DESCRIBE avvistamenti");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "✅ Colonne nella tabella: " . implode(", ", $columns);
    } else {
        echo "❌ Tabella 'avvistamenti' non trovata!";
    }
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='color: red; font-family: Arial; padding: 20px;'>";
    echo "❌ Errore di connessione: " . $e->getMessage();
    echo "</div>";
}
?>