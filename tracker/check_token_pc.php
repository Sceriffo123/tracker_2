<?php
if (isset($_COOKIE['device_token'])) {
    echo "Il token del dispositivo \u00e8: " . htmlspecialchars($_COOKIE['device_token']);
} else {
    echo "Nessun token trovato.";
}
?>
