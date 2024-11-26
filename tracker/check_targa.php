<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config/database.php';
require_once 'includes/classes/VehicleManager.php';

header('Content-Type: application/json');

if (isset($_GET['targa'])) {
    // Debug input
    file_put_contents('debug.log', "Ricevuta richiesta per targa: " . $_GET['targa'] . "\n", FILE_APPEND);
    
    $vehicleManager = new VehicleManager($conn);
    $result = $vehicleManager->checkTarga($_GET['targa']);
    
    // Debug risultato query
    file_put_contents('debug.log', "Risultato query: " . print_r($result, true) . "\n", FILE_APPEND);
    
    $response = [
        'exists' => !empty($result),
        'data' => $result
    ];
    
    // Debug risposta finale
    file_put_contents('debug.log', "Risposta inviata: " . json_encode($response) . "\n", FILE_APPEND);
    
    echo json_encode($response);
}