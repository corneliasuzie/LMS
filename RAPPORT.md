# Rapport de projet

Titre: LMS refondu - Prototype

Étudiant: MASSONGO SUZANNE NENICIA
Matricule: 24G2845
Université: Université de Yaoundé I
Examinateur: Dr MESSI

Résumé:
Ce projet propose une version simplifiée d'un LMS (Learning Management System) conçue pour démontrer l'usage combiné de HTML/CSS/JavaScript côté client et PHP/MySQL côté serveur. L'interface est légère, centrée sur l'expérience, et permet d'afficher des cours, de s'inscrire et de voir sa progression.

Fonctionnalités principales:
- Authentification basique (session PHP)
- Liste des cours (API REST)
- Inscription aux cours
- Base de données MySQL avec schéma fourni

Architecture technique:
- Frontend: `index.php`, `assets/css/style.css`, `assets/js/app.js`.
- Backend: `api/*.php` (endpoints JSON).
- DB: `sql/migrate.sql`.

Tests effectués:
- Importation de la migration et vérification des tables.
- Navigation sur `http://localhost/lms/` et interactions (login, inscription).

Améliorations futures:
- Password hashing et gestion utilisateurs plus complète.
- Pages d'administration pour ajout de contenus.
- Notifications et suivi détaillé de progression.

Conclusion:
Prototype fonctionnel prêt pour démonstration et extensible selon les exigences du cours.
