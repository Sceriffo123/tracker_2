<?php
session_start();

// Se l'utente è già loggato, redirect alla dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Tracker - Sistema di Monitoraggio Veicoli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
                        url('assets/images/hero-bg.jpg') center/cover;
            min-height: 100vh;
            color: white;
        }
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #0d6efd;
        }
        .cta-button {
            padding: 1rem 2rem;
            font-size: 1.2rem;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero d-flex align-items-center">
        <div class="container text-center">
            <h1 class="display-2 mb-4">Vehicle Tracker</h1>
            <p class="lead mb-5">Sistema avanzato di monitoraggio e gestione veicoli in tempo reale</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="login.php" class="btn btn-primary cta-button">Accedi</a>
                <a href="register.php" class="btn btn-outline-light cta-button">Registrati</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Caratteristiche Principali</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="feature-icon">🚗</div>
                        <h3>Monitoraggio in Tempo Reale</h3>
                        <p>Traccia e monitora i veicoli in tempo reale con aggiornamenti istantanei sulla loro posizione.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="feature-icon">📱</div>
                        <h3>Multi-dispositivo</h3>
                        <p>Accedi al sistema da qualsiasi dispositivo con verifica di sicurezza avanzata.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="feature-icon">📊</div>
                        <h3>Analisi Dettagliate</h3>
                        <p>Genera report dettagliati e analisi statistiche sui movimenti dei veicoli.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Vantaggi del Sistema</h2>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="p-4 border rounded h-100">
                        <h4>Per gli Amministratori</h4>
                        <ul class="list-unstyled">
                            <li class="mb-2">✓ Gestione completa degli utenti</li>
                            <li class="mb-2">✓ Controllo degli accessi</li>
                            <li class="mb-2">✓ Monitoraggio delle attività</li>
                            <li class="mb-2">✓ Report personalizzati</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-4 border rounded h-100">
                        <h4>Per gli Operatori</h4>
                        <ul class="list-unstyled">
                            <li class="mb-2">✓ Interfaccia intuitiva</li>
                            <li class="mb-2">✓ Inserimento rapido dei dati</li>
                            <li class="mb-2">✓ Ricerca avanzata</li>
                            <li class="mb-2">✓ Notifiche in tempo reale</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Vehicle Tracker - Tutti i diritti riservati</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>