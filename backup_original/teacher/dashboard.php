<?php
// ============================================
// teacher/dashboard.php - Tableau de bord enseignant
// ============================================
require_once '../config.php';

if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] != 'enseignant') {
    header("Location: ../index.php");
    exit();
}

$enseignant_id = $_SESSION['utilisateur_id'];

// Compter les cours de cet enseignant
$nb_cours = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM cours WHERE enseignant_id=$enseignant_id"))['total'];
$nb_lecons = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM lecons l JOIN cours c ON l.cours_id=c.id WHERE c.enseignant_id=$enseignant_id"))['total'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enseignant - Tableau de bord</title>
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
    <h1>Bonjour, <?= $_SESSION['nom'] ?> 👋</h1>
    <p style="color:#777; margin-bottom:20px;">Espace Enseignant</p>

    <!-- Statistiques -->
    <div class="stats-grille">
        <div class="stat-carte">
            <div class="nombre"><?= $nb_cours ?></div>
            <div class="libelle">Cours créés</div>
        </div>
        <div class="stat-carte">
            <div class="nombre"><?= $nb_lecons ?></div>
            <div class="libelle">Leçons</div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="card">
        <h2>Que voulez-vous faire ?</h2>
        <a href="courses.php" class="btn btn-bleu">📚 Voir mes cours</a>
        <a href="add_course.php" class="btn btn-vert">➕ Créer un cours</a>
    </div>

    <!-- Mes cours récents -->
    <div class="card">
        <h2>Mes derniers cours</h2>
        <?php
        $cours = mysqli_query($conn, "SELECT c.*, m.titre as module_titre FROM cours c JOIN modules m ON c.module_id = m.id WHERE c.enseignant_id=$enseignant_id ORDER BY c.date_creation DESC LIMIT 5");
        if (mysqli_num_rows($cours) == 0): ?>
            <p style="color:#888;">Vous n'avez pas encore de cours. <a href="add_course.php">Créer votre premier cours</a></p>
        <?php else: ?>
            <?php while ($c = mysqli_fetch_assoc($cours)): ?>
                <div class="liste-item">
                    <div>
                        <div class="titre-item">📘 <?= $c['titre'] ?></div>
                        <div class="info-item">Module : <?= $c['module_titre'] ?></div>
                    </div>
                    <div>
                        <a href="lessons.php?cours_id=<?= $c['id'] ?>" class="btn btn-bleu" style="font-size:12px">Gérer les leçons</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
