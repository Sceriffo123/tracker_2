<?php
// File: login.php
require 'db_connection.php';
require_once 'device_verification.php';
session_start();

// Se l'utente è già loggato, redirect alla dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$success_message = "";

// Gestione messaggi dalla registrazione
if (isset($_GET['message']) && $_GET['message'] === 'registration_success') {
    $success_message = "Registrazione completata. Attendi l'approvazione dell'amministratore per accedere.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Prepara e esegui la query
        $sql = "SELECT * FROM utenti WHERE username = :username";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Verifica lo stato dell'account
            if ($user['stato'] === 'in attesa') {
                $error = "Il tuo account è in attesa di approvazione da parte dell'amministratore.";
            } elseif ($user['stato'] === 'sospeso') {
                $error = "Il tuo account è stato sospeso. Contatta l'amministratore.";
            } elseif ($user['stato'] === 'attivo') {
                // Inizializza il gestore dispositivi
                $deviceManager = new DeviceManager($conn);
                
                // Ottieni l'impronta del dispositivo
                $deviceInfo = $deviceManager->generateDeviceFingerprint();
                
                // Verifica se il dispositivo è autorizzato
                if ($deviceManager->isDeviceAuthorized($user['id'], $deviceInfo['fingerprint'])) {
                    // Dispositivo autorizzato - procedi con il login
                    $device_token = bin2hex(random_bytes(16));
                    
                    // Aggiorna il token del dispositivo e l'ultimo accesso
                    $update_sql = "UPDATE utenti SET 
                        device_token = :token, 
                        ultimo_accesso = CURRENT_TIMESTAMP 
                        WHERE id = :user_id";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bindParam(':token', $device_token);
                    $update_stmt->bindParam(':user_id', $user['id']);
                    $update_stmt->execute();

                    // Imposta il cookie per 30 giorni
                    setcookie("device_token", $device_token, time() + (30 * 24 * 60 * 60), "/");

                    // Imposta le variabili di sessione
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['ruolo'] = $user['ruolo'];

                    // Log dell'accesso riuscito
                    $log_message = "Accesso effettuato da: " . $user['username'] . " con ruolo: " . $user['ruolo'];
                    error_log($log_message);

                    // Redirect alla dashboard
                    header("Location: dashboard.php");
                    exit();
                } else {
                    // Dispositivo non autorizzato - registra il nuovo dispositivo
                    $token = $deviceManager->registerNewDevice($user['id'], $deviceInfo);
                    
                    // Mostra messaggio di attesa autorizzazione
                    $error = "Questo dispositivo non è ancora autorizzato.<br>";
                    $error .= "Il tuo codice di verifica è: " . $token . "<br>";
                    $error .= "Contatta l'amministratore per l'autorizzazione.";
                }
            }
        } else {
            $error = "Username o password non validi.";
        }
    } catch (PDOException $e) {
        $error = "Errore del server. Riprova più tardi.";
        error_log("Errore login: " . $e->getMessage());
    }
}

// Array per la visualizzazione dei ruoli
$ruoli_display = [
    'admin' => 'Amministratore',
    'esperto' => 'Operatore Esperto',
    'standard' => 'Operatore Inseritore',
    'base' => 'Ospite'
];
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script>
        // Script per raccogliere informazioni sul dispositivo
        function getDeviceInfo() {
            document.getElementById('screen_resolution').value = 
                window.screen.width + 'x' + window.screen.height;
            document.getElementById('timezone').value = 
                Intl.DateTimeFormat().resolvedOptions().timeZone;
        }
    </script>
    <style>
        .error { color: red; }
        .success { color: green; }
        .container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            width: 100%;
        }
        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body onload="getDeviceInfo()">
    <div class="container">
        <h2>Login</h2>
        
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required 
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <!-- Campi nascosti per le informazioni del dispositivo -->
            <input type="hidden" id="screen_resolution" name="screen_resolution">
            <input type="hidden" id="timezone" name="timezone">

            <button type="submit" class="button">Accedi</button>
        </form>

        <p>Non hai un account? <a href="register.php">Registrati</a></p>
    </div>
</body>
</html>