<?php
// ============================================
// student/progress.php - État d'avancement des cours de l'étudiant
// ============================================
require_once '../config.php';

if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] != 'etudiant') {
    header("Location: ../index.php");
    exit();
}

$etudiant_id = $_SESSION['utilisateur_id'];

// Récupérer la progression pour chaque cours suivi
$sql = "SELECT c.id as cours_id, c.titre as cours_titre, m.titre as module_titre,
        (SELECT COUNT(*) FROM lecons WHERE cours_id = c.id) as total_lecons,
        (SELECT COUNT(*) FROM progression p JOIN lecons l ON p.lecon_id = l.id WHERE l.cours_id = c.id AND p.etudiant_id = $etudiant_id AND p.completee = 1) as completed_lecons
        FROM cours c
        JOIN modules m ON c.module_id = m.id
        ORDER BY m.titre, c.titre";
$res = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>LMS - Ma Progression</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav>
    <a href="dashboard.php" class="logo">🎓 LMS Étudiant</a>
    <div>
        <a href="courses.php">Cours</a>
        <a href="progress.php">Ma progression</a>
        <a href="certificates.php">Mes certificats</a>
        <a href="../logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">
    <h1>📈 Ma progression d'apprentissage</h1>
    <p style="color:#777; margin-bottom:20px;">Retrouvez l'état de complétion de chacun de vos cours.</p>

    <div class="card">
        <h2>Avancement par cours</h2>
        <?php if (mysqli_num_rows($res) == 0): ?>
            <p style="color:#888;">Aucun cours disponible sur la plateforme.</p>
        <?php else: ?>
            <?php while ($row = mysqli_fetch_assoc($res)): 
                $total = $row['total_lecons'];
                $completed = $row['completed_lecons'];
                $pourcentage = ($total > 0) ? round(($completed / $total) * 100) : 0;
            ?>
                <div style="border-bottom: 1px solid #eee; padding: 15px 0; margin-bottom: 10px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <strong><?= $row['cours_titre'] ?></strong>
                        <span style="font-size:13px; color:#666;">Module : <?= $row['module_titre'] ?></span>
                    </div>
                    <div class="barre-conteneur">
                        <div class="barre-progression" style="width: <?= $pourcentage ?>%">
                            <?= $pourcentage > 10 ? $pourcentage.'%' : '' ?>
                        </div>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:12px; color:#777;">
                        <span><?= $completed ?> / <?= $total ?> leçons complétées</span>
                        <span><?= $pourcentage ?>% achevé</span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
