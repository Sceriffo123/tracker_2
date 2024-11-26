<?php
// File: gestione_dispositivi.php
require 'db_connection.php';
require 'device_verification.php';

session_start();

// Verifica che l'utente sia admin
if (!isset($_SESSION['user_id']) || $_SESSION['ruolo'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$deviceManager = new DeviceManager($conn);

// Gestione autorizzazione dispositivo
if (isset($_POST['authorize_device'])) {
    $token = $_POST['device_token'];
    $deviceManager->authorizeDevice($token);
}

// Recupera tutti i dispositivi in attesa
$sql = "SELECT d.*, u.username 
        FROM dispositivi_autorizzati d 
        JOIN utenti u ON d.user_id = u.id 
        WHERE d.stato = 'in_attesa'
        ORDER BY d.data_registrazione DESC";
        
$devices = $conn->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Dispositivi</title>
    <style>
        .device-card {
            border: 1px solid #ddd;
            margin: 10px;
            padding: 15px;
        }
    </style>
</head>
<body>
    <h2>Dispositivi in Attesa di Autorizzazione</h2>
    
    <?php foreach ($devices as $device): ?>
        <div class="device-card">
            <h3>Utente: <?php echo htmlspecialchars($device['username']); ?></h3>
            <p>IP: <?php echo htmlspecialchars($device['ip_address']); ?></p>
            <p>Browser: <?php echo htmlspecialchars($device['user_agent']); ?></p>
            <p>Data richiesta: <?php echo $device['data_registrazione']; ?></p>
            
            <form method="POST">
                <input type="hidden" name="device_token" 
                       value="<?php echo htmlspecialchars($device['device_token']); ?>">
                <button type="submit" name="authorize_device">Autorizza Dispositivo</button>
            </form>
        </div>
    <?php endforeach; ?>
    
    <?php if (empty($devices)): ?>
        <p>Nessun dispositivo in attesa di autorizzazione.</p>
    <?php endif; ?>
    
    <p><a href="dashboard.php">Torna alla Dashboard</a></p>
</body>
</html>