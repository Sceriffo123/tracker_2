<?php
class VehicleManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function saveVehicle($data) {
        try {
            $sql = "INSERT INTO veicoli (targa, marca, modello, anno, colore) 
                    VALUES (:targa, :marca, :modello, :anno, :colore)";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':targa' => $data['targa'],
                ':marca' => $data['marca'],
                ':modello' => $data['modello'],
                ':anno' => $data['anno'],
                ':colore' => $data['colore']
            ]);
        } catch (PDOException $e) {
            throw new Exception("Errore nel salvataggio del veicolo: " . $e->getMessage());
        }
    }

    public function checkTarga($targa) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM veicoli WHERE targa = :targa");
            $stmt->execute([':targa' => $targa]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Errore nella verifica della targa: " . $e->getMessage());
        }
    }
}
?>