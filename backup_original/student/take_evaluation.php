<?php
// ============================================
// student/take_evaluation.php - Passage d'une évaluation par un étudiant
// ============================================
require_once '../config.php';

if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] != 'etudiant') {
    header("Location: ../index.php");
    exit();
}

$etudiant_id = $_SESSION['utilisateur_id'];
$eval_id = intval($_GET['id'] ?? 0);

// Charger l'évaluation et la leçon associée
$sql = "SELECT e.*, l.titre as lecon_titre, l.id as lecon_id FROM evaluations e 
        JOIN lecons l ON e.lecon_id = l.id 
        WHERE e.id = $eval_id";
$res = mysqli_query($conn, $sql);
$eval = mysqli_fetch_assoc($res);

if (!$eval) {
    header("Location: courses.php");
    exit();
}

$lecon_id = $eval['lecon_id'];

// Charger toutes les questions de l'évaluation
$questions_res = mysqli_query($conn, "SELECT * FROM questions WHERE evaluation_id = $eval_id");
$total_questions = mysqli_num_rows($questions_res);

$score = 0;
$correction_mode = false;
$user_answers = [];
$erreur = "";

// Traiter les réponses soumises par l'étudiant
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reponses_soumises = $_POST['questions'] ?? [];
    
    if (count($reponses_soumises) < $total_questions) {
        $erreur = "Veuillez répondre à toutes les questions.";
    } else {
        $correction_mode = true;
        $bonnes_reponses = 0;

        foreach ($reponses_soumises as $q_id => $r_id) {
            $q_id = intval($q_id);
            $r_id = intval($r_id);
            $user_answers[$q_id] = $r_id;

            // Vérifier si la réponse choisie est la bonne
            $check_sql = "SELECT est_correcte FROM reponses WHERE id = $r_id AND question_id = $q_id";
            $check_res = mysqli_query($conn, $check_sql);
            $check = mysqli_fetch_assoc($check_res);

            if ($check && $check['est_correcte'] == 1) {
                $bonnes_reponses++;
            }
        }

        // Calculer la note en pourcentage (%)
        $score = ($total_questions > 0) ? round(($bonnes_reponses / $total_questions) * 100) : 0;

        // Enregistrer la progression dans la base de données
        $completee = 1;
        $insert_prog = "INSERT INTO progression (etudiant_id, lecon_id, note, completee) 
                        VALUES ($etudiant_id, $lecon_id, $score, $completee)
                        ON DUPLICATE KEY UPDATE note = $score, completee = $completee";
        mysqli_query($conn, $insert_prog);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Évaluation - <?= $eval['titre'] ?></title>
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
    <h1>📝 <?= $eval['titre'] ?></h1>
    <p style="color:#777; margin-bottom:20px;">Leçon : <?= $eval['lecon_titre'] ?></p>

    <?php if ($erreur): ?>
        <div class="alerte alerte-erreur"><?= $erreur ?></div>
    <?php endif; ?>

    <?php if ($correction_mode): ?>
        <!-- Affichage des résultats après soumission -->
        <div class="card">
            <h2>Résultat de l'évaluation</h2>
            <div class="barre-conteneur">
                <div class="barre-progression" style="width: <?= $score ?>%">
                    <?= $score ?>%
                </div>
            </div>
            <p style="font-size:16px; margin:15px 0;">
                Votre note : <strong><?= $score ?>%</strong>
            </p>
            <?php if ($score >= 50): ?>
                <div class="alerte alerte-succes">
                    🎉 Félicitations ! Vous avez validé cette leçon.
                </div>
            <?php else: ?>
                <div class="alerte alerte-erreur">
                    ⚠️ Vous n'avez pas obtenu la moyenne (50%). Relisez le cours et réessayez.
                </div>
            <?php endif; ?>
            <br>
            <a href="courses.php" class="btn btn-bleu">Retour aux cours</a>
            <a href="take_evaluation.php?id=<?= $eval_id ?>" class="btn btn-gris">Refaire l'évaluation</a>
        </div>
    <?php else: ?>
        <!-- Formulaire de l'évaluation -->
        <?php if ($total_questions == 0): ?>
            <div class="card">
                <p style="color:#888;">Il n'y a pas encore de questions dans cette évaluation. Veuillez contacter votre enseignant.</p>
                <a href="courses.php" class="btn btn-gris">Retour</a>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <?php 
                $q_num = 1;
                mysqli_data_seek($questions_res, 0);
                while ($q = mysqli_fetch_assoc($questions_res)): 
                ?>
                    <div class="card">
                        <p style="font-weight:bold; font-size:16px; margin-bottom:12px;">
                            Question <?= $q_num ?> : <?= $q['question'] ?>
                        </p>
                        
                        <?php
                        // Charger les propositions de réponses
                        $reponses_res = mysqli_query($conn, "SELECT * FROM reponses WHERE question_id = {$q['id']}");
                        while ($r = mysqli_fetch_assoc($reponses_res)):
                        ?>
                            <div class="form-group" style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                                <input type="radio" name="questions[<?= $q['id'] ?>]" value="<?= $r['id'] ?>" id="rep_<?= $r['id'] ?>" required>
                                <label for="rep_<?= $r['id'] ?>" style="font-weight:normal; margin-bottom:0; cursor:pointer;">
                                    <?= $r['texte_reponse'] ?>
                                </label>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php 
                $q_num++;
                endwhile; 
                ?>
                
                <button type="submit" class="btn btn-vert" style="width:100%; padding:12px;">Soumettre mes réponses</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>
