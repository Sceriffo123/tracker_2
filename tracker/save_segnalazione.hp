<?php
// save_vehicle.php
require_once 'includes/config/database.php';
require_once 'includes/classes/VehicleManager.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Debug iniziale
error_log("Avvio save_vehicle.php - POST data: " . print_r($_POST, true));
error_log("File data: " . print_r($_FILES, true));

try {
    $conn->beginTransaction();

    // 1. Debug autorizzazione
    error_log("Tentativo inserimento autorizzazione: " . $_POST['numero_autorizzazione'] . " comune: " . $_POST['comune_id']);
    
    // Inserimento autorizzazione
    $stmt = $conn->prepare("
        INSERT INTO autorizzazioni (numero_autorizzazione, comune_id)
        VALUES (:numero_autorizzazione, :comune_id)
    ");
    $stmt->execute([
        'numero_autorizzazione' => $_POST['numero_autorizzazione'],
        'comune_id' => $_POST['comune_id']
    ]);
    $autorizzazione_id = $conn->lastInsertId();
    error_log("Autorizzazione inserita con ID: " . $autorizzazione_id);

    // 2. Debug veicolo
    error_log("Tentativo inserimento veicolo con targa: " . $_POST['targa']);
    
    // Inserimento veicolo
    $stmt = $conn->prepare("
        INSERT INTO targa_autorizzazioni (
            targa, 
            tipo_mezzo_id, 
            marca_id, 
            modello_id, 
            autorizzazione_id,
            data_inizio,
            created_by
        ) VALUES (
            :targa,
            :tipo_mezzo_id,
            :marca_id,
            :modello_id,
            :autorizzazione_id,
            NOW(),
            :created_by
        )
    ");

    $stmt->execute([
        'targa' => $_POST['targa'],
        'tipo_mezzo_id' => $_POST['tipo_mezzo'],
        'marca_id' => $_POST['marca_id'],
        'modello_id' => $_POST['modello_id'],
        'autorizzazione_id' => $autorizzazione_id,
        'created_by' => $_SESSION['user_id']
    ]);
    $veicolo_id = $conn->lastInsertId();
    error_log("Veicolo inserito con ID: " . $veicolo_id);

    // 3. Gestione foto
    $foto_path = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/photos/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        error_log("Directory upload: " . $upload_dir);

        $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_path = $upload_dir . uniqid() . '.' . $file_extension;
        error_log("Tentativo upload foto in: " . $foto_path);
        
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path)) {
            throw new Exception('Errore nel caricamento della foto');
        }
        error_log("Foto caricata con successo in: " . $foto_path);
    }

    // 4. Inserimento avvistamento
    error_log("Tentativo inserimento avvistamento per veicolo ID: " . $veicolo_id);
    $stmt = $conn->prepare("
        INSERT INTO avvistamenti (
            veicolo_id,
            data_avvistamento,
            citta,
            indirizzo,
            foto_path,
            note,
            created_by
        ) VALUES (
            :veicolo_id,
            NOW(),
            :citta,
            :indirizzo,
            :foto_path,
            :note,
            :created_by
        )
    ");

    $stmt->execute([
        'veicolo_id' => $veicolo_id,
        'citta' => $_POST['citta_avvistamento'],
        'indirizzo' => $_POST['indirizzo'],
        'foto_path' => $foto_path,
        'note' => $_POST['note'] ?? null,
        'created_by' => $_SESSION['user_id']
    ]);
    error_log("Avvistamento inserito con successo");

    $conn->commit();
    error_log("Transazione completata con successo");
    header('Location: form_veicoli.php?success=1&message=Veicolo inserito con successo');
    exit();

} catch (Exception $e) {
    $conn->rollBack();
    error_log("ERRORE in save_vehicle.php: " . $e->getMessage());
    
    if (isset($foto_path) && file_exists($foto_path)) {
        unlink($foto_path);
        error_log("Foto eliminata dopo errore: " . $foto_path);
    }
    
    header('Location: form_veicoli.php?error=' . urlencode($e->getMessage()));
    exit();
}