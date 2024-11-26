<?php
class AuthorizationManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAuthorizations($page = 1, $per_page = 10, $filters = []) {
        $offset = ($page - 1) * $per_page;
        $where_conditions = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where_conditions[] = "(at.numero_autorizzazione LIKE :search OR at.ragione_sociale LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['stato'])) {
            $where_conditions[] = "at.stato = :stato";
            $params[':stato'] = $filters['stato'];
        }

        $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

        $sql = "SELECT at.*, 
                c.comune as comune_nome,
                GROUP_CONCAT(DISTINCT CONCAT(aa.nome, ' ', aa.cognome) SEPARATOR ', ') as autisti
                FROM autorizzazioni_titolarita at
                LEFT JOIN comuni c ON at.comune_id = c.id
                LEFT JOIN autorizzazioni_autisti aa ON at.id = aa.autorizzazione_titolarita_id
                $where_clause
                GROUP BY at.id
                ORDER BY at.created_at DESC
                LIMIT :offset, :per_page";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount($filters = []) {
        $where_conditions = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where_conditions[] = "(numero_autorizzazione LIKE :search OR ragione_sociale LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['stato'])) {
            $where_conditions[] = "stato = :stato";
            $params[':stato'] = $filters['stato'];
        }

        $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
        
        $sql = "SELECT COUNT(*) FROM autorizzazioni_titolarita $where_clause";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn();
    }

    public function saveAuthorization($data) {
        $sql = "INSERT INTO autorizzazioni_titolarita (
                    numero_autorizzazione, comune_id, ragione_sociale, 
                    tipo_titolare, tipo_ottenimento, indirizzo,
                    sede_operativa, rimessa_comunale, rimessa_provinciale,
                    created_by
                ) VALUES (
                    :numero_autorizzazione, :comune_id, :ragione_sociale,
                    :tipo_titolare, :tipo_ottenimento, :indirizzo,
                    :sede_operativa, :rimessa_comunale, :rimessa_provinciale,
                    :created_by
                )";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    public function getAuthorizationById($id) {
        $sql = "SELECT at.*, c.comune as comune_nome
                FROM autorizzazioni_titolarita at
                LEFT JOIN comuni c ON at.comune_id = c.id
                WHERE at.id = :id";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateAuthorization($id, $data) {
        $sql = "UPDATE autorizzazioni_titolarita SET
                numero_autorizzazione = :numero_autorizzazione,
                comune_id = :comune_id,
                ragione_sociale = :ragione_sociale,
                tipo_titolare = :tipo_titolare,
                tipo_ottenimento = :tipo_ottenimento,
                indirizzo = :indirizzo,
                sede_operativa = :sede_operativa,
                rimessa_comunale = :rimessa_comunale,
                rimessa_provinciale = :rimessa_provinciale
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $data[':id'] = $id;
        return $stmt->execute($data);
    }
}
?>