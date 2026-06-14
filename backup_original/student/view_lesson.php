<?php
// ============================================
// student/view_lesson.php - Consultation d'une leçon (PDF/Vidéo)
// ============================================
require_once '../config.php';

if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] != 'etudiant') {
    header("Location: ../index.php");
    exit();
}

$etudiant_id = $_SESSION['utilisateur_id'];
$lecon_id = intval($_GET['id'] ?? 0);

// Charger les informations de la leçon
$sql = "SELECT l.*, c.titre as cours_titre, c.id as cours_id, e.id as evaluation_id 
        FROM lecons l 
        JOIN cours c ON l.cours_id = c.id 
        LEFT JOIN evaluations e ON l.id = e.lecon_id 
        WHERE l.id = $lecon_id";
$res = mysqli_query($conn, $sql);
$lecon = mysqli_fetch_assoc($res);

if (!$lecon) {
    header("Location: courses.php");
    exit();
}

// Vérifier si l'étudiant a déjà complété cette leçon
$prog_sql = "SELECT * FROM progression WHERE etudiant_id = $etudiant_id AND lecon_id = $lecon_id";
$prog_res = mysqli_query($conn, $prog_sql);
$progression = mysqli_fetch_assoc($prog_res);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>LMS - <?= $lecon['titre'] ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .media-container {
            margin: 20px 0;
            background: #eee;
            border-radius: 5px;
            overflow: hidden;
            text-align: center;
        }
        .pdf-frame {
            width: 100%;
            height: 600px;
            border: none;
        }
        .video-player {
            width: 100%;
            max-height: 500px;
            outline: none;
        }
    </style>
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
    <p style="color:#888; font-size:13px">
        <a href="courses.php">Tous les cours</a> → <?= $lecon['cours_titre'] ?>
    </p>
    <h1>📖 <?= $lecon['titre'] ?></h1>
    <p style="color:#777;">Type de support : <span class="badge badge-<?= $lecon['type_contenu'] ?>"><?= strtoupper($lecon['type_contenu']) ?></span></p>

    <!-- Affichage du média (PDF ou Vidéo) -->
    <div class="card media-container">
        <?php if ($lecon['type_contenu'] == 'pdf'): ?>
            <iframe class="pdf-frame" src="../uploads/<?= $lecon['fichier'] ?>"></iframe>
        <?php else: ?>
            <video class="video-player" controls>
                <source src="../uploads/<?= $lecon['fichier'] ?>" type="video/mp4">
                Votre navigateur ne supporte pas la lecture de vidéos.
            </video>
        <?php endif; ?>
    </div>

    <!-- Section Évaluation -->
    <div class="card">
        <h2>📝 Évaluation de la leçon</h2>
        <?php if ($progression && $progression['completee'] == 1): ?>
            <div class="alerte alerte-succes">
                Vous avez déjà complété cette leçon ! <br>
                <strong>Votre score : <?= $progression['note'] ?>%</strong>
            </div>
            <a href="courses.php" class="btn btn-gris">Retour aux cours</a>
        <?php else: ?>
            <?php if ($lecon['evaluation_id']): ?>
                <p style="margin-bottom:15px; color:#555;">Une fois que vous avez fini d'étudier le support ci-dessus, cliquez sur le bouton ci-dessous pour passer l'évaluation et valider votre progression.</p>
                <a href="take_evaluation.php?id=<?= $lecon['evaluation_id'] ?>" class="btn btn-vert">Passer l'évaluation</a>
            <?php else: ?>
                <div class="alerte alerte-info">
                    Aucune évaluation n'est encore définie pour cette leçon. Vous pouvez revenir plus tard ou continuer.
                </div>
                <!-- Si pas d'évaluation, permettre de la marquer comme lue directement -->
                <form method="POST" action="../ajax/valider_sans_eval.php">
                    <input type="hidden" name="lecon_id" value="<?= $lecon['id'] ?>">
                    <button type="submit" class="btn btn-bleu">Marquer cette leçon comme complétée</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
