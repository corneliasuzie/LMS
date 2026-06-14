<?php
// ============================================
// student/certificates.php - Certificats obtenus par l'étudiant
// ============================================
require_once '../config.php';

if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] != 'etudiant') {
    header("Location: ../index.php");
    exit();
}

$etudiant_id = $_SESSION['utilisateur_id'];

// Récupérer les certificats de l'étudiant
$sql = "SELECT c.*, m.titre as module_titre, m.description as module_desc, u.nom as admin_nom FROM certificats c 
        JOIN modules m ON c.module_id = m.id 
        LEFT JOIN users u ON m.admin_id = u.id
        WHERE c.etudiant_id = $etudiant_id 
        ORDER BY c.date_attribution DESC";
$res = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>LMS - Mes Certificats</title>
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
    <h1>🏆 Mes Certificats</h1>
    <p style="color:#777; margin-bottom:20px;">Retrouvez ici les modules que vous avez validés avec succès.</p>

    <?php if (mysqli_num_rows($res) == 0): ?>
        <div class="card">
            <p style="color:#888; text-align:center; padding:20px;">Vous n'avez pas encore obtenu de certificat. Complétez tous les cours et évaluations d'un module pour demander sa validation par le promoteur.</p>
        </div>
    <?php else: ?>
        <?php while ($c = mysqli_fetch_assoc($res)): ?>
            <div class="card certificat-boite">
                <h2>🏆 CERTIFICAT DE RÉUSSITE 🏆</h2>
                <p style="font-size:14px; margin-top:10px;">Le présent document atteste que l'étudiant(e)</p>
                <h1 style="font-size:28px; margin: 15px 0; font-family:'Georgia', serif; font-style:italic;">
                    <?= $_SESSION['nom'] ?>
                </h1>
                <p style="font-size:14px;">a brillamment suivi et validé l'intégralité du module d'enseignement :</p>
                <h3 style="font-size:22px; color:#2c3e50; margin:10px 0; font-weight:bold;">
                    <?= $c['module_titre'] ?>
                </h3>
                <p style="font-size:12px; color:#666; margin-top:20px;">
                    Délivré le : <strong><?= date('d/m/Y', strtotime($c['date_attribution'])) ?></strong>
                </p>
                <p style="font-size:11px; color:#999; margin-top:5px;">
                    Par le promoteur de l'Université de Yaoundé 1 : <?= $c['admin_nom'] ?: 'LMS Promoteur' ?>
                </p>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

</body>
</html>
