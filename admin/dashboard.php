<?php
// ============================================
// admin/dashboard.php - Tableau de bord du promoteur (admin)
// ============================================
require_once '../config.php';

// Vérifier que l'utilisateur est connecté et est bien admin
if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Compter les statistiques générales
$nb_modules = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM modules"))['total'];
$nb_etudiants = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='etudiant'"))['total'];
$nb_enseignants = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='enseignant'"))['total'];
$nb_certificats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM certificats"))['total'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Tableau de bord</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<!-- Barre de navigation -->
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
    <h1>Bonjour, <?= $_SESSION['nom'] ?> 👋</h1>
    <p style="color:#777; margin-bottom:20px;">Espace Promoteur - Gestion de la plateforme</p>

    <!-- Statistiques -->
    <div class="stats-grille">
        <div class="stat-carte">
            <div class="nombre"><?= $nb_modules ?></div>
            <div class="libelle">Modules</div>
        </div>
        <div class="stat-carte">
            <div class="nombre"><?= $nb_etudiants ?></div>
            <div class="libelle">Étudiants</div>
        </div>
        <div class="stat-carte">
            <div class="nombre"><?= $nb_enseignants ?></div>
            <div class="libelle">Enseignants</div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="card">
        <h2>Actions rapides</h2>
        <a href="modules.php" class="btn btn-bleu">📦 Gérer les modules</a>
        <a href="certificates.php" class="btn btn-vert">🏆 Attribuer des certificats</a>
        <a href="users.php" class="btn btn-gris">👥 Voir les utilisateurs</a>
    </div>

    <!-- Liste des modules -->
    <div class="card">
        <h2>Modules récents</h2>
        <?php
        $modules = mysqli_query($conn, "SELECT m.*, u.nom as admin_nom FROM modules m JOIN users u ON m.admin_id = u.id ORDER BY m.date_creation DESC LIMIT 5");
        if (mysqli_num_rows($modules) == 0): ?>
            <p style="color:#888;">Aucun module créé pour l'instant.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Titre du module</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
                <?php while ($m = mysqli_fetch_assoc($modules)): ?>
                <tr>
                    <td><?= $m['titre'] ?></td>
                    <td><?= $m['description'] ?></td>
                    <td>
                        <a href="certificates.php?module_id=<?= $m['id'] ?>" class="btn btn-vert" style="font-size:12px">🏆 Certificats</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
