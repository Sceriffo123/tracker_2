<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Verifica accesso
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['ruolo'], ['admin', 'standard', 'esperto'])) {
   header("Location: login.php");
   exit();
}

require_once 'includes/config/database.php';
require_once 'includes/classes/VehicleManager.php';

$vehicleManager = new VehicleManager($conn);
$pageTitle = 'Gestione Veicoli';

// Gestione messaggi
$success_message = '';
$error_message = '';

if (isset($_GET['success'])) {
   if (isset($_GET['message'])) {
       $success_message = htmlspecialchars($_GET['message']);
   } else {
       $success_message = "Operazione completata con successo!";
   }
}

if (isset($_GET['error'])) {
   $error_message = htmlspecialchars($_GET['error']);
}

$stmt = $conn->query("SELECT id, comune, provincia FROM comuni ORDER BY comune");
$comuni = $stmt->fetchAll();

// Array città principali per avvistamenti
$citta_principali = ['Firenze', 'Milano', 'Ravenna', 'Riccione'];
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Vehicle Tracker</title>
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
        .header-banner {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .search-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 1rem;
        }
        .search-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        }
        .form-control, .form-select {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
            border-color: #0d6efd;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .alert {
            border-radius: 0.5rem;
            border: none;
        }
        .photo-preview {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }
        @media (max-width: 768px) {
            .header-banner {
                padding: 1.5rem;
                text-align: center;
            }
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Header Banner -->
        <div class="header-banner shadow-sm">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-6 mb-0">Gestione Veicoli</h1>
                    <p class="mb-0 mt-2">Ricerca e registrazione veicoli nel sistema</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="dashboard.php" class="btn btn-light">
                        <i class="bi bi-arrow-left"></i> Torna alla Dashboard
                    </a>
                </div>
            </div>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Search Card -->
        <div class="card search-card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="bi bi-search me-2"></i>
                    Ricerca Veicolo
                </h5>
                <form id="searchForm">
                    <div class="form-group">
                        <label for="targa" class="form-label">Targa</label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="targa" 
                               name="targa" 
                               placeholder="Inserisci la targa del veicolo"
                               required
                               maxlength="7"
                               autocomplete="off">
                    </div>
                </form>
                <div id="targaResult" class="mt-3"></div>
            </div>
        </div>

        <!-- Vehicle Form -->
        <div id="newVehicleForm" style="display:none;">
            <div class="card search-card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nuovo Veicolo
                    </h5>
                    <form method="POST" action="save_vehicle.php" class="needs-validation" enctype="multipart/form-data" novalidate>
                        <input type="hidden" id="targa_submit" name="targa">

                        <div class="row g-3">
                            <!-- Comune Autorizzazione -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-building me-1"></i>
                                    Comune Autorizzazione
                                </label>
                                <select name="comune_id" class="form-select" required>
                                    <option value="">Seleziona comune...</option>
                                    <?php foreach ($comuni as $comune): ?>
                                    <option value="<?php echo $comune['id']; ?>">
                                        <?php echo htmlspecialchars($comune['comune'] . ' (' . $comune['provincia'] . ')'); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Numero Autorizzazione -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-hash me-1"></i>
                                    Numero Autorizzazione
                                </label>
                                <input type="text" name="numero_autorizzazione" class="form-control" required>
                            </div>

                            <!-- Tipo Veicolo -->
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="bi bi-truck me-1"></i>
                                    Tipo Veicolo
                                </label>
                                <select class="form-select" id="tipo_mezzo" name="tipo_mezzo" required>
                                    <option value="">Seleziona tipo...</option>
                                    <?php foreach ($vehicleManager->getTipiMezzi() as $tipo): ?>
                                        <option value="<?php echo $tipo['id']; ?>">
                                            <?php echo htmlspecialchars($tipo['tipo']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Marca -->
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="bi bi-tag me-1"></i>
                                    Marca
                                </label>
                                <select class="form-select" id="marca" name="marca_id" required>
                                    <option value="">Seleziona marca...</option>
                                    <?php foreach ($vehicleManager->getMarche() as $marca): ?>
                                        <option value="<?php echo $marca['id']; ?>">
                                            <?php echo htmlspecialchars($marca['marca']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Modello -->
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="bi bi-car-front me-1"></i>
                                    Modello
                                </label>
                                <select class="form-select" id="modello" name="modello_id" required>
                                    <option value="">Prima seleziona una marca...</option>
                                </select>
                            </div>

                            <!-- Città Avvistamento -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    Città Avvistamento
                                </label>
                                <select name="citta_avvistamento" class="form-select" required>
                                    <?php foreach ($citta_principali as $citta): ?>
                                    <option value="<?php echo $citta; ?>"><?php echo $citta; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Indirizzo -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-signpost me-1"></i>
                                    Indirizzo Avvistamento
                                </label>
                                <input type="text" name="indirizzo" class="form-control" required>
                            </div>

                            <!-- Tipo Avvistamento -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-eye me-1"></i>
                                    Tipo Avvistamento
                                </label>
                                <select name="tipo_avvistamento" class="form-select" required>
                                    <option value="parcheggiato">Parcheggiato</option>
                                    <option value="in movimento">In Movimento</option>
                                    <option value="al semaforo">Al Semaforo</option>
                                    <option value="altro">Altro</option>
                                </select>
                            </div>

                            <!-- Foto -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-camera me-1"></i>
                                    Foto
                                </label>
                                <input type="file" name="foto" class="form-control" accept="image/*" onchange="previewPhoto(this)">
                                <img id="photoPreview" class="photo-preview" style="display: none;">
                            </div>

                            <!-- Note -->
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="bi bi-pencil me-1"></i>
                                    Note
                                </label>
                                <textarea name="note" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bi bi-save me-2"></i>
                                    Salva Veicolo
                                </button>
                                <button type="reset" class="btn btn-secondary" onclick="document.getElementById('photoPreview').style.display='none';">
                                    <i class="bi bi-x-circle me-2"></i>
                                    Cancella
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.getElementById('searchForm');
        const targaInput = document.getElementById('targa');
        const targaResult = document.getElementById('targaResult');
        const newVehicleForm = document.getElementById('newVehicleForm');
        const targa_submit = document.getElementById('targa_submit');

        // Funzione per mostrare il form di segnalazione
        window.mostraSegnalazione = function(targa) {
            const now = new Date().toISOString().slice(0, 16);
            targaResult.innerHTML += `
                <div class="card mt-3 search-card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-plus-circle me-2"></i>
                            Nuova Segnalazione per ${targa}
                        </h5>
                        <form id="segnalazioneForm" method="POST" action="save_segnalazione.php" enctype="multipart/form-data">
                            <input type="hidden" name="targa" value="${targa}">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        Data e Ora
                                    </label>
                                    <input type="datetime-local" name="data_ora" class="form-control" value="${now}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        Città Avvistamento
                                    </label>
                                    <select name="citta_avvistamento" class="form-select" required>
                                        <?php foreach ($citta_principali as $citta): ?>
                                        <option value="<?php echo $citta; ?>"><?php echo $citta; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-signpost me-1"></i>
                                        Indirizzo
                                    </label>
                                    <input type="text" name="indirizzo" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-eye me-1"></i>
                                        Tipo Avvistamento
                                    </label>
                                    <select name="tipo_avvistamento" class="form-select" required>
                                        <option value="parcheggiato">Parcheggiato</option>
                                        <option value="in movimento">In Movimento</option>
                                        <option value="al semaforo">Al Semaforo</option>
                                        <option value="altro">Altro</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">
                                        <i class="bi bi-camera me-1"></i>
                                        Foto
                                    </label>
                                    <input type="file" name="foto" class="form-control" accept="image/*">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">
                                        <i class="bi bi-pencil me-1"></i>
                                        Note
                                    </label>
                                    <textarea name="note" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>
                                        Salva Segnalazione
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>`;
        };

        targaInput.addEventListener('input', function() {
            const targa = this.value.trim().toUpperCase();
            this.value = targa;
            
            if (targa.length < 7) {
                targaResult.innerHTML = `
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Inserisci la targa completa (7 caratteri) - Caratteri inseriti: ${targa.length}/7
                    </div>`;
                newVehicleForm.style.display = 'none';
                return;
            }

            if (targa.length === 7) {
                fetch(`check_targa.php?targa=${targa}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists && data.data) {
                            targaResult.innerHTML = `
                                <div class="alert alert-success">
                                    <h5><i class="bi bi-check-circle me-2"></i>Veicolo trovato:</h5>
                                    <p>
                                        <i class="bi bi-truck me-2"></i>Tipo: ${data.data.tipo_mezzo}<br>
                                        <i class="bi bi-tag me-2"></i>Marca: ${data.data.marca}<br>
                                        <i class="bi bi-car-front me-2"></i>Modello: ${data.data.modello}<br>
                                        <i class="bi bi-hash me-2"></i>Autorizzazione: ${data.data.numero_autorizzazione}<br>
                                        ${data.data.comune_autorizzante ? 
                                            `<i class="bi bi-building me-2"></i>Comune Autorizzazione: ${data.data.comune_autorizzante}` : 
                                            `<span class="text-danger">⚠️ Manca il comune che ha emesso l'autorizzazione ${data.data.numero_autorizzazione}. Di quale comune è?</span>`
                                        }
                                    </p>
                                    <button type="button" class="btn btn-primary mt-3" onclick="mostraSegnalazione('${targa}')">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        Aggiungi Segnalazione
                                    </button>
                                </div>`;
                            newVehicleForm.style.display = 'none';
                        } else {
                            targaResult.innerHTML = `
                                <div class="alert alert-warning">
                                    <h5><i class="bi bi-exclamation-triangle me-2"></i>Targa non trovata</h5>
                                    <p>La targa ${targa} non è presente nel database.</p>
                                    <p>Compila il form sotto per inserire il nuovo veicolo.</p>
                                </div>`;
                            newVehicleForm.style.display = 'block';
                            targa_submit.value = targa;
                        }
                    })
                    .catch(error => {
                        console.error('Errore:', error);
                        targaResult.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                Errore durante la verifica della targa.
                            </div>`;
                    });
            }
        });

        // Gestione cambio marca per popolamento modelli
        document.getElementById('marca').addEventListener('change', function() {
            const marca_id = this.value;
            const modello_select = document.getElementById('modello');
            
            if (marca_id) {
                fetch(`get_modelli.php?marca_id=${marca_id}`)
                    .then(response => response.json())
                    .then(data => {
                        modello_select.innerHTML = '<option value="">Seleziona modello...</option>';
                        data.forEach(modello => {
                            modello_select.innerHTML += `
                                <option value="${modello.id}">
                                    ${modello.modello}
                                </option>`;
                        });
                    });
            } else {
                modello_select.innerHTML = '<option value="">Prima seleziona una marca...</option>';
            }
        });
    });

    // Preview foto
    function previewPhoto(input) {
        const preview = document.getElementById('photoPreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</body>
</html>