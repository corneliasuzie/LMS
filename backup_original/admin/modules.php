<?php
// ============================================
// admin/modules.php - Gestion des modules par le promoteur
// ============================================
require_once '../config.php';

if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$succes = "";
$erreur = "";

// Supprimer un module
if (isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    mysqli_query($conn, "DELETE FROM modules WHERE id = $id");
    $succes = "Module supprimé.";
}

// Ajouter un nouveau module
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);

    if (empty($titre)) {
        $erreur = "Le titre est obligatoire.";
    } else {
        $admin_id = $_SESSION['utilisateur_id'];
        $sql = "INSERT INTO modules (titre, description, admin_id) VALUES ('$titre', '$description', $admin_id)";
        if (mysqli_query($conn, $sql)) {
            $succes = "Module ajouté avec succès !";
        } else {
            $erreur = "Erreur lors de l'ajout du module.";
        }
    }
}

// Récupérer tous les modules
$modules = mysqli_query($conn, "SELECT m.*, u.nom as admin_nom FROM modules m JOIN users u ON m.admin_id = u.id ORDER BY m.date_creation DESC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Modules</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav>
    <a href="dashboard.php" class="logo">🎓 LMS Admin</a>
    <div>
        <a href="modules.php">Modules</a>
        <a href="users.php">Utilisateurs</a>
        <a href="certificates.php">Certificats</a>
        <a href="../logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container">
    <h1>📦 Gestion des modules</h1>

    <?php if ($succes): ?>
        <div class="alerte alerte-succes"><?= $succes ?></div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div class="alerte alerte-erreur"><?= $erreur ?></div>
    <?php endif; ?>

    <!-- Formulaire d'ajout de module -->
    <div class="card">
        <h2>Ajouter un nouveau module</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label>Titre du module *</label>
                <input type="text" name="titre" placeholder="Ex: Développement Web" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Décrivez ce module..."></textarea>
            </div>
            <button type="submit" class="btn btn-bleu">Ajouter le module</button>
        </form>
    </div>

    <!-- Liste des modules existants -->
    <div class="card">
        <h2>Liste des modules</h2>
        <?php if (mysqli_num_rows($modules) == 0): ?>
            <p style="color:#888;">Aucun module pour l'instant. Ajoutez-en un ci-dessus.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
                <?php while ($m = mysqli_fetch_assoc($modules)): ?>
                <tr>
                    <td><?= $m['titre'] ?></td>
                    <td><?= $m['description'] ?: '-' ?></td>
                    <td>
                        <a href="certificates.php?module_id=<?= $m['id'] ?>" class="btn btn-vert" style="font-size:12px">🏆 Certificats</a>
                        <a href="?supprimer=<?= $m['id'] ?>" class="btn btn-rouge" style="font-size:12px" onclick="return confirm('Supprimer ce module ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
