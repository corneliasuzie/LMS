<?php
// ============================================
// admin/users.php - Liste des utilisateurs
// ============================================
require_once '../config.php';

if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Récupérer tous les utilisateurs sauf l'admin connecté
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY role, nom");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Utilisateurs</title>
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
    <h1>👥 Liste des utilisateurs</h1>

    <div class="card">
        <table>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Date d'inscription</th>
            </tr>
            <?php while ($u = mysqli_fetch_assoc($users)): ?>
            <tr>
                <td><?= $u['nom'] ?></td>
                <td><?= $u['email'] ?></td>
                <td>
                    <?php
                    // Afficher le rôle en français avec un badge coloré
                    if ($u['role'] == 'admin') echo '<span class="badge badge-valide">Promoteur</span>';
                    elseif ($u['role'] == 'enseignant') echo '<span class="badge badge-pdf">Enseignant</span>';
                    else echo '<span class="badge badge-video">Étudiant</span>';
                    ?>
                </td>
                <td><?= date('d/m/Y', strtotime($u['date_creation'])) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
