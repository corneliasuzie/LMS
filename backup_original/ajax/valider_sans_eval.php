<?php
// ============================================
// ajax/valider_sans_eval.php - Permet de marquer une leçon sans évaluation comme lue
// ============================================
require_once '../config.php';

if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] != 'etudiant') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $etudiant_id = $_SESSION['utilisateur_id'];
    $lecon_id = intval($_POST['lecon_id'] ?? 0);

    if ($lecon_id > 0) {
        // Enregistrer la progression à 100% de réussite (car pas d'évaluation)
        $sql = "INSERT INTO progression (etudiant_id, lecon_id, note, completee) 
                VALUES ($etudiant_id, $lecon_id, 100.00, 1)
                ON DUPLICATE KEY UPDATE note = 100.00, completee = 1";
        mysqli_query($conn, $sql);
    }
}

header("Location: ../student/courses.php");
exit();
?>
