<?php
// File: register.php
require 'db_connection.php';
session_start();

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST['email'];
    $ruolo = 'base'; // Tutti i nuovi utenti partono come base/ospite

    // Validazione della password
    if (strlen($password) < 8) {
        $message = "La password deve essere di almeno 8 caratteri.";
    } elseif ($password !== $confirm_password) {
        $message = "Le password non coincidono.";
    } else {
        $sql_check = "SELECT * FROM utenti WHERE username = :username OR email = :email";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bindParam(':username', $username);
        $stmt_check->bindParam(':email', $email);
        $stmt_check->execute();

        if ($stmt_check->rowCount() > 0) {
            $message = "Username o email già esistenti.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $device_token = bin2hex(random_bytes(16));
            
            $sql_insert = "INSERT INTO utenti (username, email, password, ruolo, stato, device_token) 
                          VALUES (:username, :email, :password, :ruolo, 'in attesa', :device_token)";
            
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bindParam(':username', $username);
            $stmt_insert->bindParam(':email', $email);
            $stmt_insert->bindParam(':password', $hashed_password);
            $stmt_insert->bindParam(':ruolo', $ruolo);
            $stmt_insert->bindParam(':device_token', $device_token);

            try {
                $stmt_insert->execute();
                setcookie("device_token", $device_token, time() + (30 * 24 * 60 * 60), "/");
                header("Location: login.php?message=registration_success");
                exit();
            } catch (PDOException $e) {
                $message = "Errore del server: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione Utente</title>
</head>
<body>
    <h2>Registrazione Utente</h2>
    <?php if (!empty($message)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    
    <form action="register.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        
        <label for="confirm_password">Conferma Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>
        
        <button type="submit">Registrati</button>
    </form>
    <p>Hai già un account? <a href="login.php">Effettua il login</a></p>
</body>
</html>