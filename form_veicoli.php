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

<!DOCTYPE html>
<html lang="it">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?php echo $pageTitle; ?></title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
   <style>
       .form-container { max-width: 800px; margin: 20px auto; padding: 20px; }
       .result-container { margin-top: 20px; }
   </style>
</head>
<body>
   <div class="container">
       <div class="form-container">
           <h2 class="mb-4">Gestione Veicoli</h2>

           <!-- Form Ricerca Targa -->
           <div class="card mb-4">
               <div class="card-body">
                   <form id="searchForm" class="mb-3">
                       <div class="form-group">
                           <label for="targa" class="form-label">Targa</label>
                           <input type="text" 
                                  class="form-control form-control-lg" 
                                  id="targa" 
                                  name="targa" 
                                  placeholder="Inserisci la targa"
                                  required
                                  maxlength="7"
                                  autocomplete="off">
                       </div>
                   </form>
                   <div id="targaResult" class="mt-3"></div>
               </div>
           </div>

           <!-- Form Nuovo Veicolo -->
           <div id="newVehicleForm" style="display:none;">
               <div class="card">
                   <div class="card-body">
                       <h5 class="card-title">Inserimento Nuovo Veicolo</h5>
                       <form method="POST" action="save_vehicle.php" class="needs-validation" enctype="multipart/form-data" novalidate>
                           <input type="hidden" id="targa_submit" name="targa">

                           <!-- Comune Autorizzazione -->
                           <div class="mb-3">
                               <label class="form-label">Comune Autorizzazione</label>
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
                           <div class="mb-3">
                               <label class="form-label">Numero Autorizzazione</label>
                               <input type="text" name="numero_autorizzazione" class="form-control" required>
                           </div>
                           
                           <!-- Tipo Veicolo -->
                           <div class="mb-3">
                               <label for="tipo_mezzo" class="form-label">Tipo Veicolo</label>
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
                           <div class="mb-3">
                               <label for="marca" class="form-label">Marca</label>
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
                           <div class="mb-3">
                               <label for="modello" class="form-label">Modello</label>
                               <select class="form-select" id="modello" name="modello_id" required>
                                   <option value="">Prima seleziona una marca...</option>
                               </select>
                           </div>

                           <!-- Città Avvistamento -->
                           <div class="mb-3">
                               <label class="form-label">Città Avvistamento</label>
                               <select name="citta_avvistamento" class="form-select" required>
                                   <?php foreach ($citta_principali as $citta): ?>
                                   <option value="<?php echo $citta; ?>"><?php echo $citta; ?></option>
                                   <?php endforeach; ?>
                               </select>
                           </div>

                           <!-- Indirizzo -->
                           <div class="mb-3">
                               <label class="form-label">Indirizzo Avvistamento</label>
                               <input type="text" name="indirizzo" class="form-control" required>
                           </div>

                           <!-- Tipo Avvistamento -->
                           <div class="mb-3">
                                <label class="form-label">Tipo Avvistamento</label>
                                    <select name="tipo_avvistamento" class="form-select" required>
                                        <option value="parcheggiato">Parcheggiato</option>
                                        <option value="in movimento">In Movimento</option>
                                        <option value="al semaforo">Al Semaforo</option>
                                        <option value="altro">Altro</option>
                                </select>
                            </div> 

                           <!-- Foto -->
                           <div class="mb-3">
                               <label class="form-label">Foto</label>
                               <input type="file" name="foto" class="form-control" accept="image/*">
                           </div>

                           <!-- Note -->
                           <div class="mb-3">
                               <label class="form-label">Note</label>
                               <textarea name="note" class="form-control" rows="3"></textarea>
                           </div>

                           <button type="submit" class="btn btn-primary">Salva Veicolo</button>
                       </form>
                   </div>
               </div>
           </div>
       </div>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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
               <div class="card mt-3">
                   <div class="card-body">
                       <h5>Nuova Segnalazione per ${targa}</h5>
                       <form id="segnalazioneForm" method="POST" action="save_segnalazione.php" enctype="multipart/form-data">
                           <input type="hidden" name="targa" value="${targa}">
                           <div class="mb-3">
                               <label class="form-label">Data e Ora</label>
                               <input type="datetime-local" name="data_ora" class="form-control" value="${now}" required>
                           </div>
                           <!-- Città Avvistamento -->
                           <div class="mb-3">
                               <label class="form-label">Città Avvistamento</label>
                               <select name="citta_avvistamento" class="form-select" required>
                                   <?php foreach ($citta_principali as $citta): ?>
                                   <option value="<?php echo $citta; ?>"><?php echo $citta; ?></option>
                                   <?php endforeach; ?>
                               </select>
                           </div>
                           <div class="mb-3">
                               <label class="form-label">Indirizzo</label>
                               <input type="text" name="indirizzo" class="form-control" required>
                           </div>
                           <div class="mb-3">
                                <label class="form-label">Tipo Avvistamento</label>
                                <select name="tipo_avvistamento" class="form-select" required>
                                    <option value="parcheggiato">Parcheggiato</option>
                                    <option value="in movimento">In Movimento</option>
                                    <option value="al semaforo">Al Semaforo</option>
                                    <option value="altro">Altro</option>
                                </select>
                            </div>
                           <div class="mb-3">
                               <label class="form-label">Foto</label>
                               <input type="file" name="foto" class="form-control" accept="image/*">
                           </div>
                           <div class="mb-3">
                               <label class="form-label">Note</label>
                               <textarea name="note" class="form-control" rows="3"></textarea>
                           </div>
                           <button type="submit" class="btn btn-primary">Salva Segnalazione</button>
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
                        <h5>Veicolo trovato:</h5>
                        <p>
                            Tipo: ${data.data.tipo_mezzo}<br>
                            Marca: ${data.data.marca}<br>
                            Modello: ${data.data.modello}<br>
                            Autorizzazione: ${data.data.numero_autorizzazione}<br>
                            ${data.data.comune_autorizzante ? 
                                `Comune Autorizzazione: ${data.data.comune_autorizzante}` : 
                                `<span class="text-danger">⚠️ Manca il comune che ha emesso l'autorizzazione ${data.data.numero_autorizzazione}. Di quale comune è?</span>`
                            }
                        </p>
                        <button type="button" class="btn btn-primary mt-3" onclick="mostraSegnalazione('${targa}')">
                            Aggiungi Segnalazione
                        </button>
                    </div>`;
                newVehicleForm.style.display = 'none';
            } else {
                targaResult.innerHTML = `
                    <div class="alert alert-warning">
                        <h5>Targa non trovata</h5>
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
   </script>
</body>
</html>