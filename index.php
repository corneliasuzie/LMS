<?php
// ============================================
// index.php - Page de connexion du LMS
// ============================================
require_once 'config.php';

// Si l'utilisateur est déjà connecté, le rediriger vers son espace
if (isset($_SESSION['utilisateur_id'])) {
    $role = $_SESSION['role'];
    if ($role == 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($role == 'enseignant') {
        header("Location: teacher/dashboard.php");
    } else {
        header("Location: student/dashboard.php");
    }
    exit();
}

$erreur = ""; // Variable pour stocker les messages d'erreur

// Traitement du formulaire quand l'utilisateur clique sur "Se connecter"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer et nettoyer les données du formulaire
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    // Vérifier que les champs ne sont pas vides
    if (empty($email) || empty($mot_de_passe)) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        <?php
        require_once 'config.php';
        ?>
        <!doctype html>
        <html lang="fr">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>LMS - Simple & Créatif</title>
            <link rel="stylesheet" href="assets/css/style.css">
            <meta name="description" content="Prototype LMS simple - frontend HTML/CSS/JS, backend PHP/MySQL">
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="brand">LMS Créatif</div>
                    <div id="userArea"></div>
                </div>

                <div class="hero card">
                    <div style="flex:1">
                        <h2>Bienvenue — cours créatifs et simples</h2>
                        <p class="muted">Utilise l'interface pour te connecter, parcourir les cours et t'inscrire.</p>
                    </div>
                    <div style="width:240px;text-align:right">
                        <button id="btnRefresh" class="btn">Rafraîchir</button>
                    </div>
                </div>

                <div class="grid">
                    <div>
                        <div class="card">
                            <h3>Cours disponibles</h3>
                            <div id="coursesList" class="small muted">Chargement...</div>
                        </div>
                        <div class="card">
                            <h3>Mes inscriptions</h3>
                            <div id="enrollments" class="small muted">Chargement...</div>
                        </div>
                    </div>
                    <aside>
                        <div class="card">
                            <h4>À propos</h4>
                            <p class="small">Projet pédagogique : frontend clair (HTML/CSS/JS) et backend en PHP/MySQL.</p>
                        </div>
                    </aside>
                </div>

                <div class="footer">Ouvre <a href="/lms/">/lms/</a> dans ton navigateur. Login test : admin@example.com / password</div>
            </div>

            <template id="loginTpl">
                <form id="loginForm" class="card" style="min-width:260px">
                    <input id="email" placeholder="email" required />
                    <input id="password" type="password" placeholder="mot de passe" required />
                    <button class="btn">Se connecter</button>
                </form>
            </template>

            <template id="userTpl">
                <div class="card small" style="display:flex;gap:10px;align-items:center">
                    <div id="userName"></div>
                    <div style="margin-left:auto"><a id="logoutBtn" href="#" class="btn">Déconnexion</a></div>
                </div>
            </template>

            <script src="assets/js/app.js"></script>
        </body>
        </html>
            <div>
