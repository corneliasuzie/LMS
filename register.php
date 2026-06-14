<?php
// ============================================
// register.php - Page d'inscription
// ============================================
require_once 'config.php';

$erreur = "";
$succes = "";

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);
    $role = $_POST['role'];

    // Vérification des champs
    if (empty($nom) || empty($email) || empty($mot_de_passe)) {
        $erreur = "Tous les champs sont obligatoires.";
    } elseif (strlen($mot_de_passe) < 4) {
        $erreur = "Le mot de passe doit avoir au moins 4 caractères.";
    } else {
        // Vérifier si l'email existe déjà
        $check = "SELECT id FROM users WHERE email = '$email'";
        $res = mysqli_query($conn, $check);

        if (mysqli_num_rows($res) > 0) {
            $erreur = "Cet email est déjà utilisé.";
        } else {
            // Insérer le nouvel utilisateur (mot de passe hashé avec MD5)
            $sql = "INSERT INTO users (nom, email, mot_de_passe, role) 
                    VALUES ('$nom', '$email', MD5('$mot_de_passe'), '$role')";
            if (mysqli_query($conn, $sql)) {
                $succes = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
            } else {
                $erreur = "Erreur lors de la création du compte.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>LMS - Inscription</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="page-connexion">
    <div class="boite-connexion">
        <h1>📚 LMS Yaoundé</h1>
        <p>Créer un nouveau compte</p>

        <?php if ($erreur): ?>
            <div class="alerte alerte-erreur"><?= $erreur ?></div>
        <?php endif; ?>

        <?php if ($succes): ?>
            <div class="alerte alerte-succes"><?= $succes ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Nom complet</label>
                <input type="text" name="nom" placeholder="Jean Dupont" required>
            </div>
            <div class="form-group">
                <label>Adresse email</label>
                <input type="email" name="email" placeholder="votre@email.com" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="mot_de_passe" placeholder="Au moins 4 caractères" required>
            </div>
            <div class="form-group">
                <label>Je suis</label>
                <select name="role">
                    <option value="etudiant">Étudiant</option>
                    <option value="enseignant">Enseignant</option>
                </select>
            </div>
            <button type="submit" class="btn btn-vert" style="width:100%">Créer mon compte</button>
        </form>

        <br>
        <p style="font-size:13px; color:#888;">
            Déjà un compte ? <a href="index.php">Se connecter</a>
        </p>
    </div>
</div>

</body>
</html>
