<?php
// ============================================
// admin/certificates.php - Attribution des certificats
// Le promoteur attribue des certificats aux étudiants
// qui ont validé un module (toutes les leçons complétées)
// ============================================
require_once '../config.php';

if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$succes = "";
$erreur = "";

// Attribuer un certificat
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $etudiant_id = intval($_POST['etudiant_id']);
    $module_id = intval($_POST['module_id']);

    // Vérifier si le certificat n'existe pas déjà
    $check = "SELECT id FROM certificats WHERE etudiant_id=$etudiant_id AND module_id=$module_id";
    $res = mysqli_query($conn, $check);

    if (mysqli_num_rows($res) > 0) {
        $erreur = "Cet étudiant a déjà un certificat pour ce module.";
    } else {
        $sql = "INSERT INTO certificats (etudiant_id, module_id) VALUES ($etudiant_id, $module_id)";
        if (mysqli_query($conn, $sql)) {
            $succes = "Certificat attribué avec succès !";
        }
    }
}

// Révoquer un certificat
if (isset($_GET['revoquer'])) {
    $id = intval($_GET['revoquer']);
    mysqli_query($conn, "DELETE FROM certificats WHERE id=$id");
    $succes = "Certificat révoqué.";
}

// Récupérer les modules, étudiants et certificats existants
$modules = mysqli_query($conn, "SELECT * FROM modules ORDER BY titre");
$etudiants = mysqli_query($conn, "SELECT * FROM users WHERE role='etudiant' ORDER BY nom");
$certificats = mysqli_query($conn, 
    "SELECT c.*, u.nom as etudiant_nom, m.titre as module_titre 
     FROM certificats c 
     JOIN users u ON c.etudiant_id = u.id 
     JOIN modules m ON c.module_id = m.id 
     ORDER BY c.date_attribution DESC");

// Calculer la progression d'un étudiant dans un module (pour aider l'admin)
// Un module est validé si l'étudiant a complété toutes les leçons avec note >= 50
function calculer_progression($conn, $etudiant_id, $module_id) {
    // Compter les leçons du module
    $sql_total = "SELECT COUNT(*) as total FROM lecons l 
                  JOIN cours c ON l.cours_id = c.id 
                  WHERE c.module_id = $module_id";
    $total = mysqli_fetch_assoc(mysqli_query($conn, $sql_total))['total'];

    if ($total == 0) return 0;

    // Compter les leçons complétées avec note >= 50
    $sql_ok = "SELECT COUNT(*) as ok FROM progression p 
               JOIN lecons l ON p.lecon_id = l.id 
               JOIN cours c ON l.cours_id = c.id 
               WHERE c.module_id = $module_id AND p.etudiant_id = $etudiant_id 
               AND p.completee = 1 AND p.note >= 50";
    $ok = mysqli_fetch_assoc(mysqli_query($conn, $sql_ok))['ok'];

    return round(($ok / $total) * 100);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Certificats</title>
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
    <h1>🏆 Gestion des certificats</h1>

    <?php if ($succes): ?>
        <div class="alerte alerte-succes"><?= $succes ?></div>
    <?php endif; ?>
    <?php if ($erreur): ?>
        <div class="alerte alerte-erreur"><?= $erreur ?></div>
    <?php endif; ?>

    <!-- Formulaire d'attribution -->
    <div class="card">
        <h2>Attribuer un certificat</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label>Étudiant</label>
                <select name="etudiant_id" required>
                    <option value="">-- Choisir un étudiant --</option>
                    <?php 
                    // Remettre le pointeur au début pour lire à nouveau
                    mysqli_data_seek($etudiants, 0);
                    while ($e = mysqli_fetch_assoc($etudiants)): ?>
                        <option value="<?= $e['id'] ?>"><?= $e['nom'] ?> (<?= $e['email'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Module validé</label>
                <select name="module_id" required>
                    <option value="">-- Choisir un module --</option>
                    <?php 
                    mysqli_data_seek($modules, 0);
                    while ($m = mysqli_fetch_assoc($modules)): ?>
                        <option value="<?= $m['id'] ?>"><?= $m['titre'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-vert">🏆 Attribuer le certificat</button>
        </form>
    </div>

    <!-- Progressions des étudiants par module -->
    <div class="card">
        <h2>Progression des étudiants par module</h2>
        <?php
        mysqli_data_seek($etudiants, 0);
        mysqli_data_seek($modules, 0);
        $tous_modules = [];
        while ($m = mysqli_fetch_assoc($modules)) $tous_modules[] = $m;

        if (empty($tous_modules)): ?>
            <p style="color:#888;">Aucun module disponible.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Étudiant</th>
                    <th>Module</th>
                    <th>Progression</th>
                    <th>Statut</th>
                </tr>
                <?php
                mysqli_data_seek($etudiants, 0);
                while ($e = mysqli_fetch_assoc($etudiants)):
                    foreach ($tous_modules as $mod):
                        $prog = calculer_progression($conn, $e['id'], $mod['id']);
                ?>
                <tr>
                    <td><?= $e['nom'] ?></td>
                    <td><?= $mod['titre'] ?></td>
                    <td>
                        <div class="barre-conteneur">
                            <div class="barre-progression" style="width:<?= $prog ?>%">
                                <?= $prog > 10 ? $prog.'%' : '' ?>
                            </div>
                        </div>
                        <?= $prog ?>%
                    </td>
                    <td>
                        <?php if ($prog >= 100): ?>
                            <span class="badge badge-valide">✅ Validé</span>
                        <?php else: ?>
                            <span class="badge badge-non-valide">En cours</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php
                    endforeach;
                endwhile; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- Liste des certificats attribués -->
    <div class="card">
        <h2>Certificats attribués</h2>
        <?php if (mysqli_num_rows($certificats) == 0): ?>
            <p style="color:#888;">Aucun certificat attribué pour l'instant.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Étudiant</th>
                    <th>Module</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
                <?php while ($c = mysqli_fetch_assoc($certificats)): ?>
                <tr>
                    <td><?= $c['etudiant_nom'] ?></td>
                    <td><?= $c['module_titre'] ?></td>
                    <td><?= date('d/m/Y', strtotime($c['date_attribution'])) ?></td>
                    <td>
                        <a href="?revoquer=<?= $c['id'] ?>" class="btn btn-rouge" style="font-size:12px" onclick="return confirm('Révoquer ce certificat ?')">Révoquer</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
