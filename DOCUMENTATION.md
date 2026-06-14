# 📚 Projet Académique LMS (Learning Management System)
**Niveau : L2 Informatique, Université de Yaoundé 1**
**Auteur : Étudiante en Informatique L2**

Ce document décrit en détail la structure, le fonctionnement et le code de l'application LMS créée pour valider le devoir de programmation. Il sert de guide pour répondre aux questions du professeur lors de la soutenance/correction et fournit des alternatives de modification.

---

## 🛠️ Technologies Utilisées
1. **HTML5** : Structure sémantique de l'application (formulaires, tables, navigation).
2. **CSS3** : Feuille de style propre, simple et basique, sans fioritures compliquées (dégradés, animations 3D, frameworks complexes) afin de refléter fidèlement un travail de niveau L2.
3. **JavaScript** : Gestion interactive côté client (comme le changement dynamique d'étiquette de fichier et la validation d'acceptation dans l'interface enseignant).
4. **PHP (version 7/8)** : Logique dynamique côté serveur (gestion des sessions, requêtes SQL, upload de fichiers, calculs de progression).
5. **MySQL** : Système de gestion de base de données relationnelle pour stocker les utilisateurs, cours, leçons, notes et certificats.

---

## 📁 Structure des Fichiers et Dossiers
Voici à quoi sert chaque fichier créé sur la plateforme :

* `/lms/` (Dossier racine de l'application)
  * `config.php` : Établit la connexion à MySQL avec `mysqli_connect` et démarre la session utilisateur (`session_start()`).
  * `index.php` : Page d'accueil avec formulaire de connexion (redirection automatique selon le rôle de l'utilisateur).
  * `register.php` : Formulaire d'inscription pour créer des comptes Étudiants ou Enseignants.
  * `logout.php` : Détruit la session active et redirige l'utilisateur vers l'accueil.
  * `database.sql` : Script d'initialisation de la base de données (tables, relations et comptes de test).
  * `css/style.css` : Feuille de style centralisée pour tous les espaces (design simple, neutre et professionnel).
  * `uploads/` (Dossier contenant les supports pédagogiques)
    * `pdfs/` : Stocke les documents PDF téléversés par les enseignants.
    * `videos/` : Stocke les vidéos téléversées par les enseignants.
  * `ajax/`
    * `valider_sans_eval.php` : Script rapide permettant de compléter une leçon sans évaluation.
  * `admin/` (Espace Promoteur)
    * `dashboard.php` : Tableau de bord affichant le nombre de modules, étudiants et enseignants.
    * `modules.php` : Interface d'ajout et de suppression des modules académiques.
    * `certificates.php` : Interface permettant d'attribuer des certificats de réussite aux étudiants et de suivre leur progression (%).
    * `users.php` : Liste de tous les utilisateurs inscrits sur la plateforme.
  * `teacher/` (Espace Enseignant)
    * `dashboard.php` : Accueil enseignant affichant des statistiques rapides.
    * `courses.php` : Liste des cours créés par l'enseignant connecté.
    * `add_course.php` : Formulaire de création de cours relié à un module.
    * `lessons.php` : Gestion des leçons d'un cours (upload de PDF ou Vidéo avec validation de type).
    * `add_evaluation.php` : Interface interactive pour créer une évaluation QCM (jusqu'à 4 propositions et indication de la bonne réponse).
  * `student/` (Espace Étudiant)
    * `dashboard.php` : Tableau de bord montrant la progression de l'étudiant dans chaque module.
    * `courses.php` : Consultation des cours disponibles et filtrage par module.
    * `view_lesson.php` : Page d'étude d'une leçon (affichage direct du PDF dans un iframe ou lecture de la vidéo intégrée).
    * `take_evaluation.php` : Passage de l'évaluation avec calcul instantané de la note en pourcentage (%) et enregistrement du score.
    * `progress.php` : Tableau récapitulatif de l'avancement dans les leçons complétées.
    * `certificates.php` : Affichage et édition du diplôme/certificat de réussite si attribué par le promoteur.

---

## 🛢️ Structure de la Base de Données (Schéma Physique)
* **`users`** : Contient les informations des utilisateurs. Le mot de passe est sécurisé avec la fonction standard `MD5()` en SQL.
* **`modules`** : Modules généraux définis par l'administrateur (ex: Algorithmique, Web, Réseaux).
* **`cours`** : Cours liés à un module et créés par un enseignant.
* **`lecons`** : Contenus de cours (PDF ou Vidéo). Stocke le chemin relatif du fichier stocké sur le serveur.
* **`evaluations`** : L'en-tête de l'évaluation associée à une leçon.
* **`questions`** : Les questions QCM formulées par l'enseignant.
* **`reponses`** : Les choix de réponses pour chaque question. La colonne `est_correcte` vaut `1` pour la bonne réponse et `0` pour les autres.
* **`progression`** : Enregistre le statut de complétion (`completee = 1`) et la note obtenue par l'étudiant.
* **`certificats`** : Liste des certifications validées et signées par le promoteur.

---

## 💡 Guide de Modifications pour le Professeur

### 1. Que répondre si le prof demande comment fonctionne l'upload de fichiers ?
Le formulaire HTML dans `teacher/lessons.php` utilise l'attribut `enctype="multipart/form-data"` qui autorise l'envoi de fichiers binaires.
En PHP, les fichiers téléversés arrivent dans la variable superglobale `$_FILES`. On vérifie leur extension et on les déplace depuis le dossier temporaire du serveur vers notre dossier cible (`/uploads/...`) à l'aide de la fonction `move_uploaded_file()`.

*Alternative si le prof demande de bloquer les fichiers trop volumineux :*
On peut ajouter une condition PHP dans `teacher/lessons.php` :
```php
if ($fichier['size'] > 10 * 1024 * 1024) { // Bloque à 10 Mo
    $erreur = "Le fichier dépasse la limite autorisée.";
}
```

### 2. Que répondre si le prof demande à quoi sert MD5 dans la base de données ?
MD5 est une fonction de hachage à sens unique. Elle transforme le mot de passe en une chaîne de 32 caractères hexadécimaux. Cela évite que les mots de passe soient écrits en texte clair dans la base de données.
*Alternative recommandée en production (mais plus complexe pour un niveau débutant) :*
Utiliser les fonctions PHP natives `password_hash()` et `password_verify()`.

### 3. Que répondre si le prof demande comment est calculé le pourcentage (%) de progression ?
Dans `student/take_evaluation.php` :
$$\text{Note en \%} = \left( \frac{\text{Nombre de réponses correctes}}{\text{Nombre total de questions}} \right) \times 100$$
Dans `student/dashboard.php`, le taux global par module est calculé en divisant le nombre de leçons validées par l'étudiant par le nombre total de leçons dans ce module.

### 4. Comment changer rapidement les couleurs de l'application ?
Toutes les couleurs sont définies dans `css/style.css`.
*   Pour changer la couleur de la barre de navigation : modifier `background-color: #2c3e50;` sous la classe `nav`.
*   Pour modifier le style de la barre de progression verte : modifier `background-color: #27ae60;` sous la classe `.barre-progression`.
*   Pour changer la couleur des boutons principaux : modifier les classes `.btn-bleu` ou `.btn-vert`.
