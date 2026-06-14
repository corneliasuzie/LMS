<?php
// ============================================
// student/courses.php - Liste des cours et leçons pour l'étudiant
// ============================================
require_once '../config.php';

if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] != 'etudiant') {
    header("Location: ../index.php");
    exit();
}

$etudiant_id = $_SESSION['utilisateur_id'];
$module_filter = isset($_GET['module_id']) ? intval($_GET['module_id']) : 0;

// Requête pour obtenir les cours filtrés ou non par module
$sql_cours = "SELECT c.*, m.titre as module_titre FROM cours c 
              JOIN modules m ON c.module_id = m.id";
if ($module_filter > 0) {
    $sql_cours .= " WHERE c.module_id = $module_filter";
}
$sql_cours .= " ORDER BY m.titre, c.titre";
$cours_res = mysqli_query($conn, $sql_cours);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>LMS - Liste des Cours</title>
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
    <h1>📚 Cours disponibles</h1>
    <p style="color:#777; margin-bottom:20px;">Choisissez un cours pour commencer à apprendre.</p>

    <!-- Filtre par module -->
    <div class="card" style="padding:15px; margin-bottom:20px;">
        <form method="GET" action="">
            <label style="margin-right:10px; font-weight:bold;">Filtrer par module :</label>
            <select name="module_id" onchange="this.form.submit()" style="width:auto; display:inline-block; padding:5px;">
                <option value="0">Tous les modules</option>
                <?php
                $modules_list = mysqli_query($conn, "SELECT * FROM modules ORDER BY titre");
                while ($m = mysqli_fetch_assoc($modules_list)) {
                    $selected = ($module_filter == $m['id']) ? "selected" : "";
                    echo "<option value='{$m['id']}' $selected>{$m['titre']}</option>";
                }
                ?>
            </select>
        </form>
    </div>

    <!-- Affichage des cours -->
    <?php if (mysqli_num_rows($cours_res) == 0): ?>
        <div class="card">
            <p style="color:#888; text-align:center;">Aucun cours trouvé pour ce module.</p>
        </div>
    <?php else: ?>
        <?php while ($c = mysqli_fetch_assoc($cours_res)): ?>
            <div class="card">
                <h2>📘 <?= $c['titre'] ?></h2>
                <p style="color:#666; font-size:14px; margin-bottom:15px;"><?= $c['description'] ?></p>
                <p style="font-size:12px; color:#888; margin-bottom:15px;">Module : <strong><?= $c['module_titre'] ?></strong></p>
                
                <h3>Leçons dans ce cours :</h3>
                <?php
                // Récupérer les leçons de ce cours
                $lecons_res = mysqli_query($conn, "SELECT l.*, p.completee, p.note FROM lecons l 
                                                  LEFT JOIN progression p ON l.id = p.lecon_id AND p.etudiant_id = $etudiant_id 
                                                  WHERE l.cours_id = {$c['id']} ORDER BY l.ordre");
                if (mysqli_num_rows($lecons_res) == 0): ?>
                    <p style="color:#999; font-size:13px; font-style:italic;">Aucune leçon pour l'instant dans ce cours.</p>
                <?php else: ?>
                    <div style="margin-top:10px;">
                        <?php while ($l = mysqli_fetch_assoc($lecons_res)): ?>
                            <div class="liste-item">
                                <div>
                                    <span class="badge badge-<?= $l['type_contenu'] ?>"><?= strtoupper($l['type_contenu']) ?></span>
                                    <span class="titre-item"><?= $l['titre'] ?></span>
                                </div>
                                <div>
                                    <?php if ($l['completee'] == 1): ?>
                                        <span class="badge badge-valide" style="margin-right:10px;">Note: <?= $l['note'] ?>% (Validé)</span>
                                    <?php endif; ?>
                                    <a href="view_lesson.php?id=<?= $l['id'] ?>" class="btn btn-bleu" style="font-size:12px;">Suivre la leçon</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

</body>
</html>
