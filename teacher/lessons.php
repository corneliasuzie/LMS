<?php
// ============================================
// teacher/lessons.php - Gestion des leçons d'un cours
// L'enseignant peut voir, ajouter et supprimer des leçons
// ============================================
require_once '../config.php';

if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] != 'enseignant') {
    header("Location: ../index.php");
    exit();
}

$enseignant_id = $_SESSION['utilisateur_id'];
$cours_id = intval($_GET['cours_id'] ?? 0);

// Vérifier que ce cours appartient bien à cet enseignant
$cours = mysqli_query($conn, "SELECT c.*, m.titre as module_titre FROM cours c JOIN modules m ON c.module_id=m.id WHERE c.id=$cours_id AND c.enseignant_id=$enseignant_id");
$cours_info = mysqli_fetch_assoc($cours);

if (!$cours_info) {
    header("Location: courses.php");
    exit();
}

$succes = "";
$erreur = "";

// Message si cours nouvellement créé
if (isset($_GET['nouveau'])) {
    $succes = "Cours créé avec succès ! Ajoutez maintenant vos leçons.";
}

// Supprimer une leçon
if (isset($_GET['supprimer_lecon'])) {
    $lecon_id = intval($_GET['supprimer_lecon']);
    // Récupérer le nom du fichier pour le supprimer du serveur
    $res = mysqli_query($conn, "SELECT fichier FROM lecons WHERE id=$lecon_id");
    $lecon = mysqli_fetch_assoc($res);
    if ($lecon && file_exists('../uploads/'.$lecon['fichier'])) {
        unlink('../uploads/'.$lecon['fichier']); // Supprimer le fichier physique
    }
    mysqli_query($conn, "DELETE FROM lecons WHERE id=$lecon_id");
    $succes = "Leçon supprimée.";
}

// Ajouter une leçon avec fichier
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titre_lecon = trim($_POST['titre_lecon']);
    $type_contenu = $_POST['type_contenu'];
    $ordre = intval($_POST['ordre'] ?? 1);

    if (empty($titre_lecon)) {
        $erreur = "Le titre de la leçon est obligatoire.";
    } elseif (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] != 0) {
        $erreur = "Veuillez sélectionner un fichier (PDF ou vidéo).";
    } else {
        $fichier = $_FILES['fichier'];
        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));

        // Vérifier le type de fichier selon le contenu choisi
        $extensions_autorisees = ($type_contenu == 'pdf') ? ['pdf'] : ['mp4', 'avi', 'mkv', 'mov', 'webm'];

        if (!in_array($extension, $extensions_autorisees)) {
            $erreur = "Type de fichier non autorisé pour ce type de contenu.";
        } else {
            // Créer un nom de fichier unique pour éviter les conflits
            $nom_fichier = time() . '_' . basename($fichier['name']);
            $dossier = ($type_contenu == 'pdf') ? '../uploads/pdfs/' : '../uploads/videos/';
            $chemin_complet = $dossier . $nom_fichier;
            $chemin_bd = ($type_contenu == 'pdf') ? 'pdfs/'.$nom_fichier : 'videos/'.$nom_fichier;

            // Déplacer le fichier uploadé vers le dossier approprié
            if (move_uploaded_file($fichier['tmp_name'], $chemin_complet)) {
                $sql = "INSERT INTO lecons (cours_id, titre, type_contenu, fichier, ordre) VALUES ($cours_id, '$titre_lecon', '$type_contenu', '$chemin_bd', $ordre)";
                if (mysqli_query($conn, $sql)) {
                    $lecon_id_new = mysqli_insert_id($conn);
                    $succes = "Leçon ajoutée ! Vous pouvez maintenant ajouter une évaluation.";
                } else {
                    $erreur = "Erreur base de données.";
                }
            } else {
                $erreur = "Erreur lors de l'upload du fichier. Vérifiez que le dossier uploads/ est accessible.";
            }
        }
    }
}

// Récupérer les leçons du cours
$lecons = mysqli_query($conn, "SELECT l.*, e.id as eval_id FROM lecons l LEFT JOIN evaluations e ON l.id=e.lecon_id WHERE l.cours_id=$cours_id ORDER BY l.ordre");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Leçons - <?= $cours_info['titre'] ?></title>
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
        <a href="courses.php">Mes cours</a> → <?= $cours_info['titre'] ?>
    </p>
    <h1>📖 Leçons : <?= $cours_info['titre'] ?></h1>
    <p style="color:#777; margin-bottom:20px">Module : <?= $cours_info['module_titre'] ?></p>

    <?php if ($succes): ?>
        <div class="alerte alerte-succes"><?= $succes ?></div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div class="alerte alerte-erreur"><?= $erreur ?></div>
    <?php endif; ?>

    <!-- Formulaire d'ajout de leçon -->
    <div class="card">
        <h2>Ajouter une leçon</h2>
        <!-- enctype="multipart/form-data" est obligatoire pour les uploads de fichiers -->
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label>Titre de la leçon *</label>
                <input type="text" name="titre_lecon" placeholder="Ex: Introduction aux variables" required>
            </div>
            <div class="form-group">
                <label>Type de contenu *</label>
                <select name="type_contenu" id="type_contenu" onchange="majEtiquette()">
                    <option value="pdf">📄 Document PDF</option>
                    <option value="video">🎬 Vidéo</option>
                </select>
            </div>
            <div class="form-group">
                <label id="label_fichier">Fichier PDF *</label>
                <input type="file" name="fichier" id="fichier" accept=".pdf" required>
                <small style="color:#888">Taille max recommandée : 50 MB</small>
            </div>
            <div class="form-group">
                <label>Ordre d'affichage</label>
                <input type="number" name="ordre" value="1" min="1">
            </div>
            <button type="submit" class="btn btn-vert">Ajouter la leçon</button>
        </form>
    </div>

    <!-- Liste des leçons existantes -->
    <div class="card">
        <h2>Leçons du cours (<?= mysqli_num_rows($lecons) ?>)</h2>
        <?php 
        if (mysqli_num_rows($lecons) == 0): ?>
            <p style="color:#888;">Aucune leçon pour l'instant. Ajoutez votre première leçon ci-dessus.</p>
        <?php else: ?>
            <?php while ($l = mysqli_fetch_assoc($lecons)): ?>
                <div class="liste-item">
                    <div>
                        <div class="titre-item">
                            <?= $l['type_contenu'] == 'pdf' ? '📄' : '🎬' ?>
                            Leçon <?= $l['ordre'] ?> : <?= $l['titre'] ?>
                        </div>
                        <div class="info-item">
                            <span class="badge badge-<?= $l['type_contenu'] ?>"><?= strtoupper($l['type_contenu']) ?></span>
                            <?php if ($l['eval_id']): ?>
                                <span class="badge badge-valide">✅ Évaluation liée</span>
                            <?php else: ?>
                                <span class="badge badge-non-valide">⚠️ Pas d'évaluation</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <?php if (!$l['eval_id']): ?>
                            <a href="add_evaluation.php?lecon_id=<?= $l['id'] ?>" class="btn btn-bleu" style="font-size:12px">+ Évaluation</a>
                        <?php else: ?>
                            <a href="add_evaluation.php?lecon_id=<?= $l['id'] ?>" class="btn btn-gris" style="font-size:12px">Voir éval.</a>
                        <?php endif; ?>
                        <a href="?cours_id=<?= $cours_id ?>&supprimer_lecon=<?= $l['id'] ?>" class="btn btn-rouge" style="font-size:12px" onclick="return confirm('Supprimer cette leçon ?')">Supprimer</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Fonction JavaScript pour changer l'étiquette et l'acceptation du fichier selon le type choisi
function majEtiquette() {
    var type = document.getElementById('type_contenu').value;
    var label = document.getElementById('label_fichier');
    var input = document.getElementById('fichier');

    if (type == 'pdf') {
        label.textContent = 'Fichier PDF *';
        input.accept = '.pdf';
    } else {
        label.textContent = 'Fichier Vidéo *';
        input.accept = '.mp4,.avi,.mkv,.mov,.webm';
    }
}
</script>

</body>
</html>
