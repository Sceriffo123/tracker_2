<?php
require_once 'includes/config/database.php';
require_once 'includes/classes/VehicleManager.php';

header('Content-Type: application/json');

if (isset($_GET['marca_id'])) {
    $vehicleManager = new VehicleManager($conn);
    $modelli = $vehicleManager->getModelli($_GET['marca_id']);
    
    echo json_encode($modelli);
}