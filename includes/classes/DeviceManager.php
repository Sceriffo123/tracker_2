<?php
class DeviceManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function saveAvvistamento($data) {
        try {
            $sql = "INSERT INTO avvistamenti (veicolo_id, data_avvistamento, comune_id, 
                    indirizzo, latitudine, longitudine, tipo_avvistamento, foto_path, 
                    note, created_by) 
                    VALUES (:veicolo_id, :data_avvistamento, :comune_id, :indirizzo, 
                    :latitudine, :longitudine, :tipo_avvistamento, :foto_path, 
                    :note, :created_by)";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            throw new Exception("Errore nel salvataggio dell'avvistamento: " . $e->getMessage());
        }
    }
}
?>