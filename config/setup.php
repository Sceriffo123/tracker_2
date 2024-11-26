<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = require_once 'database.php';

try {
    // Connessione al database esistente
    $conn = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}", 
        $config['username'], 
        $config['password']
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Creazione della tabella avvistamenti
    $sql = "CREATE TABLE IF NOT EXISTS avvistamenti (
        id INT AUTO_INCREMENT PRIMARY KEY,
        veicolo_id INT NOT NULL,
        data_avvistamento DATETIME NOT NULL,
        comune_id INT NOT NULL,
        indirizzo VARCHAR(255),
        latitudine DECIMAL(10, 8),
        longitudine DECIMAL(11, 8),
        tipo_avvistamento VARCHAR(50),
        foto_path VARCHAR(255),
        note TEXT,
        created_by INT NOT NULL,
        data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_veicolo (veicolo_id),
        INDEX idx_comune (comune_id),
        INDEX idx_data_avvistamento (data_avvistamento)
    )";
    $conn->exec($sql);
    echo "✅ Tabella avvistamenti creata con successo!<br>";
    
    // Creazione cartella uploads se non esiste
    if (!file_exists('../uploads')) {
        mkdir('../uploads', 0777, true);
        echo "✅ Cartella uploads creata con successo!<br>";
    }
    
} catch(PDOException $e) {
    die("❌ Errore durante il setup: " . $e->getMessage());
}
?>