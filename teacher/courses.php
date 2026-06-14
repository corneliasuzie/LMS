<?php
// ============================================
// teacher/courses.php - Liste des cours de l'enseignant
// ============================================
require_once '../config.php';

if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] != 'enseignant') {
    header("Location: ../index.php");
    exit();
}

$enseignant_id = $_SESSION['utilisateur_id'];
$succes = "";

// Supprimer un cours
if (isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    mysqli_query($conn, "DELETE FROM cours WHERE id=$id AND enseignant_id=$enseignant_id");
    $succes = "Cours supprimé.";
}

// Récupérer les cours de l'enseignant
$cours = mysqli_query($conn, "SELECT c.*, m.titre as module_titre FROM cours c JOIN modules m ON c.module_id=m.id WHERE c.enseignant_id=$enseignant_id ORDER BY c.date_creation DESC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes cours</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav>
    <a href="dashboard.php" class="logo">🎓 LMS Enseignant</a>
    <div>
        <a href="courses.php">Mes cours</a>
        <a href="../logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">
    <h1>📚 Mes cours</h1>

    <?php if ($succes): ?>
        <div class="alerte alerte-succes"><?= $succes ?></div>
    <?php endif; ?>

    <a href="add_course.php" class="btn btn-vert" style="margin-bottom:20px">➕ Créer un nouveau cours</a>

    <?php if (mysqli_num_rows($cours) == 0): ?>
        <div class="card">
            <p style="color:#888; text-align:center">Vous n'avez pas encore de cours.</p>
        </div>
    <?php else: ?>
        <?php while ($c = mysqli_fetch_assoc($cours)): ?>
            <div class="liste-item">
                <div>
                    <div class="titre-item">📘 <?= $c['titre'] ?></div>
                    <div class="info-item">Module : <?= $c['module_titre'] ?> | <?= $c['description'] ?></div>
                </div>
                <div>
                    <a href="lessons.php?cours_id=<?= $c['id'] ?>" class="btn btn-bleu" style="font-size:12px">Leçons</a>
                    <a href="?supprimer=<?= $c['id'] ?>" class="btn btn-rouge" style="font-size:12px" onclick="return confirm('Supprimer ce cours et toutes ses leçons ?')">Supprimer</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

</body>
</html>
