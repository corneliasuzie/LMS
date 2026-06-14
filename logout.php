<?php
// ============================================
// logout.php - Déconnexion
// ============================================
require_once 'config.php';

// Détruire toutes les données de session (déconnecter l'utilisateur)
session_destroy();

// Rediriger vers la page de connexion
header("Location: index.php");
exit();
?>
