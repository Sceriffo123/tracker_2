<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config/database.php';

try {
    // La connessione è già stabilita nel file database.php
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