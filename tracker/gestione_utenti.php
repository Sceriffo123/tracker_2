<?php
require 'db_connection.php';
session_start();

// Verifica accesso amministratore
if (!isset($_SESSION['user_id']) || $_SESSION['ruolo'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approva']) || isset($_POST['sospendi'])) {
        $user_id = $_POST['user_id'];
        $nuovo_stato = isset($_POST['approva']) ? 'attivo' : 'sospeso';
        
        $sql = "UPDATE utenti SET stato = :stato WHERE id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':stato', $nuovo_stato);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $message = "Stato utente aggiornato con successo.";
    } elseif (isset($_POST['modifica_ruolo'])) {
        $user_id = $_POST['user_id'];
        $nuovo_ruolo = $_POST['nuovo_ruolo'];
        
        $sql = "UPDATE utenti SET ruolo = :ruolo WHERE id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ruolo', $nuovo_ruolo);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $message = "Ruolo utente aggiornato con successo.";
    }
}

// Recupera lista utenti
$sql = "SELECT * FROM utenti ORDER BY data_registrazione DESC";
$utenti = $conn->query($sql)->fetchAll();

// Array per la visualizzazione dei ruoli
$ruoli_display = [
    'admin' => ['nome' => 'Amministratore', 'badge' => 'danger'],
    'esperto' => ['nome' => 'Operatore Esperto', 'badge' => 'success'],
    'standard' => ['nome' => 'Operatore Inseritore', 'badge' => 'primary'],
    'base' => ['nome' => 'Ospite', 'badge' => 'secondary']
];

// Array per la visualizzazione degli stati
$stati_display = [
    'attivo' => ['nome' => 'Attivo', 'badge' => 'success'],
    'sospeso' => ['nome' => 'Sospeso', 'badge' => 'danger'],
    'in attesa' => ['nome' => 'In Attesa', 'badge' => 'warning']
];
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Utenti - Vehicle Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-bg: #f8f9fa;
            --card-hover: #e9ecef;
        }
        body {
            background-color: var(--primary-bg);
        }
        .user-card {
            transition: all 0.3s ease;
            border: none;
        }
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        }
        .header-banner {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .action-button {
            transition: all 0.2s ease;
        }
        .action-button:hover {
            transform: translateY(-2px);
        }
        .role-select {
            max-width: 200px;
        }
        @media (max-width: 768px) {
            .user-actions {
                flex-direction: column;
                gap: 0.5rem;
            }
            .role-select {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Header Banner -->
        <div class="header-banner shadow-sm mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-6 mb-0">Gestione Utenti</h1>
                    <p class="mb-0 mt-2">Gestisci gli account e i permessi degli utenti del sistema</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="dashboard.php" class="btn btn-light">
                        <i class="bi bi-arrow-left"></i> Torna alla Dashboard
                    </a>
                </div>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Users Grid -->
        <div class="row g-4">
            <?php foreach ($utenti as $utente): ?>
                <div class="col-12">
                    <div class="card user-card shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="bi bi-person-circle text-primary" style="font-size: 2rem;"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1"><?php echo htmlspecialchars($utente['username']); ?></h5>
                                            <p class="mb-0 text-muted"><?php echo htmlspecialchars($utente['email']); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-2 mb-md-0">
                                        <span class="badge bg-<?php echo $ruoli_display[$utente['ruolo']]['badge']; ?> mb-2 d-inline-block">
                                            <?php echo $ruoli_display[$utente['ruolo']]['nome']; ?>
                                        </span>
                                        <span class="badge bg-<?php echo $stati_display[$utente['stato']]['badge']; ?> ms-2">
                                            <?php echo $stati_display[$utente['stato']]['nome']; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">
                                        <i class="bi bi-clock me-1"></i>
                                        Ultimo accesso:
                                        <?php echo $utente['ultimo_accesso'] ? date('d/m/Y H:i', strtotime($utente['ultimo_accesso'])) : 'Mai'; ?>
                                    </small>
                                </div>
                                <div class="col-md-3">
                                    <form action="gestione_utenti.php" method="POST" class="d-flex gap-2 user-actions">
                                        <input type="hidden" name="user_id" value="<?php echo $utente['id']; ?>">
                                        
                                        <?php if ($utente['stato'] !== 'attivo'): ?>
                                            <button type="submit" name="approva" class="btn btn-success btn-sm action-button">
                                                <i class="bi bi-check-circle"></i> Attiva
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="sospendi" class="btn btn-danger btn-sm action-button">
                                                <i class="bi bi-x-circle"></i> Sospendi
                                            </button>
                                        <?php endif; ?>
                                        
                                        <select name="nuovo_ruolo" class="form-select form-select-sm role-select">
                                            <?php foreach ($ruoli_display as $key => $value): ?>
                                                <option value="<?php echo $key; ?>" <?php echo $utente['ruolo'] === $key ? 'selected' : ''; ?>>
                                                    <?php echo $value['nome']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        
                                        <button type="submit" name="modifica_ruolo" class="btn btn-primary btn-sm action-button">
                                            <i class="bi bi-save"></i> Aggiorna
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>