<?php
// ============================================
// teacher/add_course.php - Création d'un nouveau cours
// ============================================
require_once '../config.php';

if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] != 'enseignant') {
    header("Location: ../index.php");
    exit();
}

$enseignant_id = $_SESSION['utilisateur_id'];
$erreur = "";
$succes = "";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $module_id = intval($_POST['module_id']);

    if (empty($titre) || $module_id == 0) {
        $erreur = "Le titre et le module sont obligatoires.";
    } else {
        $sql = "INSERT INTO cours (module_id, enseignant_id, titre, description) VALUES ($module_id, $enseignant_id, '$titre', '$description')";
        if (mysqli_query($conn, $sql)) {
            $cours_id = mysqli_insert_id($conn); // Récupérer l'ID du cours créé
            header("Location: lessons.php?cours_id=$cours_id&nouveau=1");
            exit();
        } else {
            $erreur = "Erreur lors de la création du cours.";
        }
    }
}

// Récupérer les modules disponibles
$modules = mysqli_query($conn, "SELECT * FROM modules ORDER BY titre");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un cours</title>
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
    <h1>➕ Créer un nouveau cours</h1>

    <?php if ($erreur): ?>
        <div class="alerte alerte-erreur"><?= $erreur ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="">
            <div class="form-group">
                <label>Titre du cours *</label>
                <input type="text" name="titre" placeholder="Ex: Introduction au HTML" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Décrivez ce cours..."></textarea>
            </div>
            <div class="form-group">
                <label>Module *</label>
                <select name="module_id" required>
                    <option value="">-- Choisir un module --</option>
                    <?php while ($m = mysqli_fetch_assoc($modules)): ?>
                        <option value="<?= $m['id'] ?>"><?= $m['titre'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <?php if (mysqli_num_rows($modules) == 0): ?>
                <div class="alerte alerte-info">
                    ⚠️ Aucun module disponible. Le promoteur (admin) doit d'abord créer des modules.
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-vert">Créer le cours</button>
            <a href="courses.php" class="btn btn-gris">Annuler</a>
        </form>
    </div>
</div>

</body>
</html>
