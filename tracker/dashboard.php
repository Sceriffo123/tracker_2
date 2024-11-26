<?php
require 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Recupera informazioni utente
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$ruolo = $_SESSION['ruolo'];

// Verifica stato utente
$sql = "SELECT stato FROM utenti WHERE id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch();

if ($user['stato'] !== 'attivo') {
    session_destroy();
    header("Location: login.php?error=account_suspended");
    exit();
}

// Funzione per verificare i permessi
function hasPermission($required_role) {
    $role_hierarchy = [
        'admin' => 4,
        'esperto' => 3,
        'standard' => 2,
        'base' => 1
    ];
    return $role_hierarchy[$_SESSION['ruolo']] >= $role_hierarchy[$required_role];
}

// Recupera statistiche
$stats = [
    'veicoli' => $conn->query("SELECT COUNT(*) as count FROM targa_autorizzazioni")->fetch()['count'],
    'avvistamenti' => $conn->query("SELECT COUNT(*) as count FROM avvistamenti")->fetch()['count'],
    'utenti' => $conn->query("SELECT COUNT(*) as count FROM utenti WHERE stato = 'attivo'")->fetch()['count']
];

// Aggiorna ultimo accesso
$update_access = "UPDATE utenti SET ultimo_accesso = CURRENT_TIMESTAMP WHERE id = :user_id";
$stmt = $conn->prepare($update_access);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Vehicle Tracker</title>
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
        .stat-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .stat-card:hover {
            background-color: var(--card-hover);
            transform: translateY(-5px);
        }
        .feature-icon {
            width: 4rem;
            height: 4rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1rem;
            background-color: #e9ecef;
        }
        .welcome-banner {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .nav-link {
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background-color: var(--card-hover);
        }
        .role-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Welcome Banner -->
        <div class="welcome-banner shadow-sm">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-6 mb-2">Benvenuto, <?php echo htmlspecialchars($username); ?>!</h1>
                    <p class="mb-0">
                        <span class="role-badge bg-light text-dark">
                            <i class="bi bi-person-badge"></i>
                            <?php 
                                $ruoli_display = [
                                    'admin' => 'Amministratore',
                                    'esperto' => 'Operatore Esperto',
                                    'standard' => 'Operatore Inseritore',
                                    'base' => 'Ospite'
                                ];
                                echo $ruoli_display[$ruolo];
                            ?>
                        </span>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="logout.php" class="btn btn-light">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card stat-card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-car-front text-primary"></i>
                        </div>
                        <h3 class="card-title"><?php echo number_format($stats['veicoli']); ?></h3>
                        <p class="card-text text-muted">Veicoli Registrati</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-eye text-success"></i>
                        </div>
                        <h3 class="card-title"><?php echo number_format($stats['avvistamenti']); ?></h3>
                        <p class="card-text text-muted">Avvistamenti Totali</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-people text-info"></i>
                        </div>
                        <h3 class="card-title"><?php echo number_format($stats['utenti']); ?></h3>
                        <p class="card-text text-muted">Utenti Attivi</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-4">
            <div class="col-12">
                <h4 class="mb-4">Azioni Rapide</h4>
            </div>
            
            <!-- Admin Actions -->
            <?php if (hasPermission('admin')): ?>
            <div class="col-md-6 col-lg-4">
                <a href="gestione_utenti.php" class="card text-decoration-none text-dark stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-people-fill text-primary me-2"></i>
                            Gestione Utenti
                        </h5>
                        <p class="card-text text-muted">Gestisci gli account e i permessi degli utenti</p>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <!-- Expert Actions -->
            <?php if (hasPermission('esperto')): ?>
            <div class="col-md-6 col-lg-4">
                <a href="gestione_avanzata.php" class="card text-decoration-none text-dark stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-gear-fill text-success me-2"></i>
                            Funzioni Avanzate
                        </h5>
                        <p class="card-text text-muted">Accedi alle funzionalità avanzate del sistema</p>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <!-- Standard Actions -->
            <?php if (hasPermission('standard')): ?>
            <div class="col-md-6 col-lg-4">
                <a href="form_veicoli.php" class="card text-decoration-none text-dark stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-plus-circle-fill text-info me-2"></i>
                            Nuovo Avvistamento
                        </h5>
                        <p class="card-text text-muted">Registra un nuovo avvistamento veicolo</p>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <!-- Base User Actions -->
            <div class="col-md-6 col-lg-4">
                <a href="visualizza_dati.php" class="card text-decoration-none text-dark stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-search text-warning me-2"></i>
                            Visualizza Dati
                        </h5>
                        <p class="card-text text-muted">Consulta i dati degli avvistamenti</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>