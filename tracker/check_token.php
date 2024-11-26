<?php
if (isset($_COOKIE['device_token'])) {
    echo "Il token del dispositivo è: " . htmlspecialchars($_COOKIE['device_token']);
} else {
    echo "Nessun token trovato. Il dispositivo non ha un token salvato.";
}
?>
