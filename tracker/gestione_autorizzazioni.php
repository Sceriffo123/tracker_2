<?php
require_once 'includes/config/database.php';
require_once 'includes/classes/AuthorizationManager.php';
session_start();

// Controllo accesso
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['ruolo'], ['admin', 'esperto'])) {
    header("Location: dashboard.php");
    exit();
}

$authManager = new AuthorizationManager($conn);
$pageTitle = 'Gestione Autorizzazioni';

// Gestione filtri e paginazione
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;

// Preparazione filtri
$filters = [];
if (isset($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}
if (isset($_GET['stato'])) {
    $filters['stato'] = $_GET['stato'];
}

// Recupero dati
$autorizzazioni = $authManager->getAuthorizations($page, $per_page, $filters);
$total_records = $authManager->getTotalCount($filters);
$total_pages = ceil($total_records / $per_page);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Vehicle Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .header-banner {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .auth-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 1rem;
        }
        .auth-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        }
        .search-box {
            position: relative;
        }
        .search-box .form-control {
            padding-left: 2.5rem;
        }
        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
        }
        .status-badge {
            padding: 0.5em 1em;
            border-radius: 2rem;
            font-size: 0.85em;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Header Banner -->
        <div class="header-banner shadow-sm">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-6 mb-0">Gestione Autorizzazioni</h1>
                    <p class="mb-0 mt-2">Gestisci le autorizzazioni e i relativi titolari</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <button type="button" class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#newAuthModal">
                        <i class="bi bi-plus-circle"></i> Nuova Autorizzazione
                    </button>
                    <a href="dashboard.php" class="btn btn-outline-light">
                        <i class="bi bi-arrow-left"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtri e Ricerca -->
        <div class="card auth-card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <div class="search-box">
                            <i class="bi bi-search text-muted"></i>
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   placeholder="Cerca per numero autorizzazione o ragione sociale"
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="stato" class="form-select">
                            <option value="">Tutti gli stati</option>
                            <option value="attiva" <?php echo isset($_GET['stato']) && $_GET['stato'] === 'attiva' ? 'selected' : ''; ?>>Attiva</option>
                            <option value="scaduta" <?php echo isset($_GET['stato']) && $_GET['stato'] === 'scaduta' ? 'selected' : ''; ?>>Scaduta</option>
                            <option value="revocata" <?php echo isset($_GET['stato']) && $_GET['stato'] === 'revocata' ? 'selected' : ''; ?>>Revocata</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filtra
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista Autorizzazioni -->
        <div class="card auth-card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Numero</th>
                                <th>Titolare</th>
                                <th>Comune</th>
                                <th>Stato</th>
                                <th>Autisti</th>
                                <th>Ultima Modifica</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($autorizzazioni as $auth): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($auth['numero_autorizzazione']); ?></td>
                                    <td><?php echo htmlspecialchars($auth['ragione_sociale']); ?></td>
                                    <td><?php echo htmlspecialchars($auth['comune_nome']); ?></td>
                                    <td>
                                        <span class="status-badge bg-<?php 
                                            echo $auth['stato'] === 'attiva' ? 'success' : 
                                                ($auth['stato'] === 'scaduta' ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst($auth['stato']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($auth['autisti'] ?: 'Nessun autista'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($auth['updated_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary"
                                                    onclick="viewAuth(<?php echo $auth['id']; ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-success"
                                                    onclick="editAuth(<?php echo $auth['id']; ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-info"
                                                    onclick="manageDrivers(<?php echo $auth['id']; ?>)">
                                                <i class="bi bi-people"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginazione -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Navigazione pagine">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?>">
                                    Precedente
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?>">
                                    Successiva
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Nuova Autorizzazione -->
    <div class="modal fade" id="newAuthModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuova Autorizzazione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newAuthForm" method="POST" action="save_autorizzazione.php">
                        <div class="row g-3">
                            <!-- Dati Autorizzazione -->
                            <div class="col-md-6">
                                <label class="form-label">Numero Autorizzazione</label>
                                <input type="text" name="numero_autorizzazione" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Comune</label>
                                <select name="comune_id" class="form-select" required>
                                    <option value="">Seleziona comune...</option>
                                    <!-- Popolato via AJAX -->
                                </select>
                            </div>

                            <!-- Dati Titolare -->
                            <div class="col-12">
                                <label class="form-label">Ragione Sociale</label>
                                <input type="text" name="ragione_sociale" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo Titolare</label>
                                <select name="tipo_titolare" class="form-select" required>
                                    <option value="persona_fisica">Persona Fisica</option>
                                    <option value="societa">Società</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo Ottenimento</label>
                                <select name="tipo_ottenimento" class="form-select" required>
                                    <option value="assegnazione">Assegnazione</option>
                                    <option value="trasferimento">Trasferimento</option>
                                </select>
                            </div>

                            <!-- Indirizzi -->
                            <div class="col-12">
                                <label class="form-label">Indirizzo</label>
                                <input type="text" name="indirizzo" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Sede Operativa</label>
                                <input type="text" name="sede_operativa" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rimessa Comunale</label>
                                <input type="text" name="rimessa_comunale" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rimessa Provinciale</label>
                                <input type="text" name="rimessa_provinciale" class="form-control">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <button type="submit" form="newAuthForm" class="btn btn-primary">Salva</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Inizializzazione Select2 per i select
        $(document).ready(function() {
            $('select[name="comune_id"]').select2({
                ajax: {
                    url: 'get_comuni.php',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                minimumInputLength: 2,
                placeholder: 'Cerca comune...'
            });
        });

        // Funzioni per la gestione delle azioni
        function viewAuth(id) {
            window.location.href = `view_autorizzazione.php?id=${id}`;
        }

        function editAuth(id) {
            window.location.href = `edit_autorizzazione.php?id=${id}`;
        }

        function manageDrivers(id) {
            window.location.href = `gestione_autisti.php?auth_id=${id}`;
        }

        // Real-time search
        let searchTimeout;
        document.querySelector('input[name="search"]').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.form.submit();
            }, 500);
        });
    </script>
</body>
</html>