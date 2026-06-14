<?php
// ============================================
// config.php - Connexion à la base de données
// Ce fichier est inclus dans tous les autres fichiers PHP
// ============================================

// Informations de connexion à MySQL
$hote = "localhost";        // Le serveur MySQL (toujours localhost en local)
$utilisateur = "root";      // Nom d'utilisateur MySQL (root par défaut avec XAMPP/WAMP)
$mot_de_passe = "";         // Mot de passe MySQL (vide par défaut avec XAMPP)
$base_de_donnees = "lms_db"; // Nom de la base de données

// Créer la connexion
$conn = mysqli_connect($hote, $utilisateur, $mot_de_passe, $base_de_donnees);

// Vérifier si la connexion a réussi
if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

// Définir l'encodage UTF-8 pour supporter les caractères spéciaux (accents, etc.)
mysqli_set_charset($conn, "utf8");

// Démarrer la session PHP (pour mémoriser l'utilisateur connecté)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
