<?php
// ============================================
// teacher/add_evaluation.php - Ajouter une évaluation à une leçon
// L'enseignant crée des questions QCM pour évaluer les étudiants
// ============================================
require_once '../config.php';

if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] != 'enseignant') {
    header("Location: ../index.php");
    exit();
}

$lecon_id = intval($_GET['lecon_id'] ?? 0);
$succes = "";
$erreur = "";

// Récupérer les infos de la leçon et du cours
$lecon = mysqli_fetch_assoc(mysqli_query($conn, "SELECT l.*, c.titre as cours_titre, c.id as cours_id FROM lecons l JOIN cours c ON l.cours_id=c.id WHERE l.id=$lecon_id"));

if (!$lecon) {
    header("Location: courses.php");
    exit();
}

// Récupérer ou créer l'évaluation liée à cette leçon
$eval = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM evaluations WHERE lecon_id=$lecon_id"));

// Créer l'évaluation si elle n'existe pas encore
if (!$eval && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['titre_eval'])) {
    $titre_eval = trim($_POST['titre_eval']);
    mysqli_query($conn, "INSERT INTO evaluations (lecon_id, titre) VALUES ($lecon_id, '$titre_eval')");
    $eval = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM evaluations WHERE lecon_id=$lecon_id"));
    $succes = "Évaluation créée ! Ajoutez maintenant des questions.";
}

// Ajouter une question avec ses réponses
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['question']) && $eval) {
    $question = trim($_POST['question']);
    $reponses = $_POST['reponses'] ?? [];
    $correcte = intval($_POST['correcte'] ?? 0);

    if (empty($question) || count($reponses) < 2) {
        $erreur = "La question et au moins 2 réponses sont obligatoires.";
    } else {
        // Insérer la question
        $sql_q = "INSERT INTO questions (evaluation_id, question) VALUES ({$eval['id']}, '$question')";
        mysqli_query($conn, $sql_q);
        $question_id = mysqli_insert_id($conn);

        // Insérer chaque réponse (marquer la correcte)
        foreach ($reponses as $i => $rep) {
            if (!empty(trim($rep))) {
                $est_correcte = ($i == $correcte) ? 1 : 0;
                $rep_clean = trim($rep);
                mysqli_query($conn, "INSERT INTO reponses (question_id, texte_reponse, est_correcte) VALUES ($question_id, '$rep_clean', $est_correcte)");
            }
        }
        $succes = "Question ajoutée avec succès !";
    }
}

// Supprimer une question
if (isset($_GET['supprimer_q'])) {
    $q_id = intval($_GET['supprimer_q']);
    mysqli_query($conn, "DELETE FROM questions WHERE id=$q_id");
    $succes = "Question supprimée.";
}

// Récupérer les questions existantes
$questions = mysqli_query($conn, "SELECT * FROM questions WHERE evaluation_id=" . ($eval['id'] ?? 0));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Évaluation - <?= $lecon['titre'] ?></title>
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
    <p style="color:#888; font-size:13px">
        <a href="lessons.php?cours_id=<?= $lecon['cours_id'] ?>"><?= $lecon['cours_titre'] ?></a> → <?= $lecon['titre'] ?>
    </p>
    <h1>📝 Évaluation : <?= $lecon['titre'] ?></h1>

    <?php if ($succes): ?>
        <div class="alerte alerte-succes"><?= $succes ?></div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div class="alerte alerte-erreur"><?= $erreur ?></div>
    <?php endif; ?>

    <?php if (!$eval): ?>
        <!-- Étape 1 : Créer l'évaluation -->
        <div class="card">
            <h2>Créer l'évaluation</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Titre de l'évaluation *</label>
                    <input type="text" name="titre_eval" placeholder="Ex: Quiz sur les variables" required>
                </div>
                <button type="submit" class="btn btn-bleu">Créer l'évaluation</button>
            </form>
        </div>
    <?php else: ?>
        <!-- Étape 2 : Ajouter des questions -->
        <div class="card">
            <h2>Ajouter une question QCM</h2>
            <p style="color:#777; font-size:13px; margin-bottom:15px">Entrez la question et 4 réponses possibles. Cochez la bonne réponse.</p>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Question *</label>
                    <input type="text" name="question" placeholder="Ex: Quelle balise HTML crée un paragraphe ?" required>
                </div>

                <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="form-group" style="display:flex; align-items:center; gap:10px">
                    <input type="radio" name="correcte" value="<?= $i ?>" <?= $i == 0 ? 'checked' : '' ?> title="Cocher si c'est la bonne réponse">
                    <input type="text" name="reponses[]" placeholder="Réponse <?= $i+1 ?>" style="flex:1">
                </div>
                <?php endfor; ?>

                <p style="font-size:12px; color:#888; margin-bottom:10px">
                    ⬤ Le bouton radio coché = la bonne réponse
                </p>
                <button type="submit" class="btn btn-vert">Ajouter la question</button>
            </form>
        </div>

        <!-- Questions existantes -->
        <div class="card">
            <h2>Questions de l'évaluation (<?= mysqli_num_rows($questions) ?>)</h2>
            <?php if (mysqli_num_rows($questions) == 0): ?>
                <p style="color:#888;">Pas encore de questions. Ajoutez-en ci-dessus.</p>
            <?php else: ?>
                <?php $n = 1; while ($q = mysqli_fetch_assoc($questions)): ?>
                    <div style="border:1px solid #ddd; border-radius:6px; padding:12px; margin-bottom:10px">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                            <div>
                                <strong>Q<?= $n ?> : <?= $q['question'] ?></strong>
                                <?php
                                // Afficher les réponses
                                $reponses = mysqli_query($conn, "SELECT * FROM reponses WHERE question_id=" . $q['id']);
                                echo "<ul style='margin-top:8px; margin-left:20px; font-size:13px'>";
                                while ($r = mysqli_fetch_assoc($reponses)) {
                                    $style = $r['est_correcte'] ? 'color:#27ae60; font-weight:bold' : 'color:#555';
                                    echo "<li style='$style'>" . ($r['est_correcte'] ? '✅ ' : '') . $r['texte_reponse'] . "</li>";
                                }
                                echo "</ul>";
                                ?>
                            </div>
                            <a href="?lecon_id=<?= $lecon_id ?>&supprimer_q=<?= $q['id'] ?>" class="btn btn-rouge" style="font-size:12px" onclick="return confirm('Supprimer cette question ?')">Supprimer</a>
                        </div>
                    </div>
                    <?php $n++; endwhile; ?>
            <?php endif; ?>
        </div>

        <a href="lessons.php?cours_id=<?= $lecon['cours_id'] ?>" class="btn btn-gris">← Retour aux leçons</a>
    <?php endif; ?>
</div>

</body>
</html>
