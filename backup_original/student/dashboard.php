<?php
// ============================================
// student/dashboard.php - Tableau de bord étudiant
// ============================================
require_once '../config.php';

if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] != 'etudiant') {
    header("Location: ../index.php");
    exit();
}

$etudiant_id = $_SESSION['utilisateur_id'];

// Compter les leçons complétées par cet étudiant
$nb_lecons_completes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM progression WHERE etudiant_id=$etudiant_id AND completee=1"))['total'];

// Compter les certificats obtenus
$nb_certificats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM certificats WHERE etudiant_id=$etudiant_id"))['total'];

// Récupérer tous les modules disponibles
$modules = mysqli_query($conn, "SELECT * FROM modules ORDER BY titre");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Étudiant - Tableau de bord</title>
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
    <h1>Bonjour, <?= $_SESSION['nom'] ?> 👋</h1>
    <p style="color:#777; margin-bottom:20px;">Espace Étudiant</p>

    <!-- Statistiques -->
    <div class="stats-grille">
        <div class="stat-carte">
            <div class="nombre"><?= $nb_lecons_completes ?></div>
            <div class="libelle">Leçons complétées</div>
        </div>
        <div class="stat-carte">
            <div class="nombre"><?= $nb_certificats ?></div>
            <div class="libelle">Certificats obtenus</div>
        </div>
    </div>

    <!-- Modules disponibles -->
    <div class="card">
        <h2>📦 Modules disponibles</h2>
        <?php if (mysqli_num_rows($modules) == 0): ?>
            <p style="color:#888;">Aucun module disponible pour l'instant.</p>
        <?php else: ?>
            <?php while ($m = mysqli_fetch_assoc($modules)): ?>

                <?php
                // Calculer la progression de l'étudiant dans ce module
                $total_lecons = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM lecons l JOIN cours c ON l.cours_id=c.id WHERE c.module_id={$m['id']}"))['t'];
                $lecons_ok = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM progression p JOIN lecons l ON p.lecon_id=l.id JOIN cours c ON l.cours_id=c.id WHERE c.module_id={$m['id']} AND p.etudiant_id=$etudiant_id AND p.completee=1"))['t'];
                $pourcentage = ($total_lecons > 0) ? round(($lecons_ok / $total_lecons) * 100) : 0;
                ?>

                <div class="liste-item" style="flex-direction:column; align-items:flex-start; gap:8px">
                    <div style="display:flex; justify-content:space-between; width:100%">
                        <div class="titre-item">📦 <?= $m['titre'] ?></div>
                        <a href="courses.php?module_id=<?= $m['id'] ?>" class="btn btn-bleu" style="font-size:12px">Voir les cours</a>
                    </div>
                    <div style="width:100%">
                        <div class="barre-conteneur">
                            <div class="barre-progression" style="width:<?= $pourcentage ?>%">
                                <?= $pourcentage > 10 ? $pourcentage.'%' : '' ?>
                            </div>
                        </div>
                        <small style="color:#777"><?= $lecons_ok ?>/<?= $total_lecons ?> leçons complétées (<?= $pourcentage ?>%)</small>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
