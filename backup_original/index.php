<?php
// ============================================
// index.php - Page de connexion du LMS
// ============================================
require_once 'config.php';

// Si l'utilisateur est déjà connecté, le rediriger vers son espace
if (isset($_SESSION['utilisateur_id'])) {
    $role = $_SESSION['role'];
    if ($role == 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($role == 'enseignant') {
        header("Location: teacher/dashboard.php");
    } else {
        header("Location: student/dashboard.php");
    }
    exit();
}

$erreur = ""; // Variable pour stocker les messages d'erreur

// Traitement du formulaire quand l'utilisateur clique sur "Se connecter"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer et nettoyer les données du formulaire
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    // Vérifier que les champs ne sont pas vides
    if (empty($email) || empty($mot_de_passe)) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        // Chercher l'utilisateur dans la base de données
        // MD5() hash le mot de passe pour comparer avec celui stocké
        $sql = "SELECT * FROM users WHERE email = '$email' AND mot_de_passe = MD5('$mot_de_passe')";
        $resultat = mysqli_query($conn, $sql);

        if (mysqli_num_rows($resultat) == 1) {
            // L'utilisateur existe → mémoriser ses infos dans la session
            $user = mysqli_fetch_assoc($resultat);
            $_SESSION['utilisateur_id'] = $user['id'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Rediriger selon le rôle
            if ($user['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } elseif ($user['role'] == 'enseignant') {
                header("Location: teacher/dashboard.php");
            } else {
                header("Location: student/dashboard.php");
            }
            exit();
        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>LMS - Connexion</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="page-connexion">
    <div class="boite-connexion">
        <h1>📚 LMS Yaoundé</h1>
        <p>Plateforme d'apprentissage en ligne</p>

        <!-- Afficher l'erreur si elle existe -->
        <?php if ($erreur): ?>
            <div class="alerte alerte-erreur"><?= $erreur ?></div>
        <?php endif; ?>

        <!-- Formulaire de connexion -->
        <form method="POST" action="">
            <div class="form-group">
                <label>Adresse email</label>
                <input type="email" name="email" placeholder="votre@email.com" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="mot_de_passe" placeholder="Votre mot de passe" required>
            </div>
            <button type="submit" class="btn btn-bleu" style="width:100%">Se connecter</button>
        </form>

        <br>
        <p style="font-size:13px; color:#888;">
            Pas encore de compte ? 
            <a href="register.php">S'inscrire</a>
        </p>

        <!-- Comptes de test pour les démonstrations -->
        <div style="margin-top:20px; padding:10px; background:#f5f5f5; border-radius:5px; font-size:12px; text-align:left;">
            <strong>Comptes de test :</strong><br>
            Admin : admin@lms.com / admin123<br>
            Enseignant : prof@lms.com / prof123<br>
            Étudiant : marie@lms.com / marie123
        </div>
    </div>
</div>

</body>
</html>
