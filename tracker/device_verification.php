<?php
// File: device_verification.php

class DeviceManager {
    private $conn;
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }
    
    public function generateDeviceFingerprint() {
        // Raccoglie informazioni sul dispositivo
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $ip = $_SERVER['REMOTE_ADDR'];
        $language = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        $screenResolution = $_POST['screen_resolution'] ?? '';
        $timezone = $_POST['timezone'] ?? '';
        
        // Crea un fingerprint unico
        $deviceData = $userAgent . $language . $screenResolution . $timezone;
        $fingerprint = hash('sha256', $deviceData);
        
        return [
            'fingerprint' => $fingerprint,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'details' => [
                'language' => $language,
                'resolution' => $screenResolution,
                'timezone' => $timezone
            ]
        ];
    }
    
    public function registerNewDevice($userId, $deviceInfo) {
        $token = bin2hex(random_bytes(32)); // Token di verifica univoco
        
        $sql = "INSERT INTO dispositivi_autorizzati 
                (user_id, device_fingerprint, device_token, ip_address, user_agent, device_details, stato)
                VALUES (:user_id, :fingerprint, :token, :ip, :agent, :details, 'in_attesa')";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':fingerprint' => $deviceInfo['fingerprint'],
            ':token' => $token,
            ':ip' => $deviceInfo['ip'],
            ':agent' => $deviceInfo['user_agent'],
            ':details' => json_encode($deviceInfo['details'])
        ]);
        
        return $token;
    }
    
    public function isDeviceAuthorized($userId, $fingerprint) {
        $sql = "SELECT stato FROM dispositivi_autorizzati 
                WHERE user_id = :user_id AND device_fingerprint = :fingerprint";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':fingerprint' => $fingerprint
        ]);
        
        $result = $stmt->fetch();
        return $result && $result['stato'] === 'autorizzato';
    }
    
    public function authorizeDevice($token) {
        $sql = "UPDATE dispositivi_autorizzati 
                SET stato = 'autorizzato', data_autorizzazione = CURRENT_TIMESTAMP 
                WHERE device_token = :token";
                
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':token' => $token]);
    }
}

// Struttura della tabella necessaria:
/*
CREATE TABLE dispositivi_autorizzati (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    device_fingerprint VARCHAR(64) NOT NULL,
    device_token VARCHAR(64) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    device_details JSON,
    stato ENUM('in_attesa', 'autorizzato', 'bloccato') NOT NULL,
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_autorizzazione TIMESTAMP NULL,
    ultimo_accesso TIMESTAMP NULL,
    UNIQUE KEY (user_id, device_fingerprint),
    FOREIGN KEY (user_id) REFERENCES utenti(id)
);
*/