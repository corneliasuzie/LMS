# Documentation technique (FR)

Résumé:
- Frontend: HTML simple + CSS minimaliste (assets/css/style.css) + JS (assets/js/app.js) utilisant fetch pour appeler l'API.
- Backend: PHP classique (mysqli) exposant des endpoints JSON sous `api/`.
- Base: MySQL (base `lms_db`), migration dans `sql/migrate.sql`.

Installation rapide:
1. Démarrer XAMPP.
2. Copier le projet dans `/opt/lampp/htdocs/lms`.
3. Importer la migration: `sudo /opt/lampp/bin/mysql -u root -S /opt/lampp/var/mysql/mysql.sock < sql/migrate.sql`.
4. Ouvrir `http://127.0.0.1/lms/`.

Fichiers importants:
- `index.php`: SPA.
- `assets/js/app.js`: logique frontend (login, lister cours, inscription).
- `api/auth.php`: login/logout/me.
- `api/courses.php`: GET/POST pour cours.
- `api/enroll.php`: GET/POST pour inscriptions/progress.
- `config.php`: paramètres DB.

Alternatives et améliorations possibles:
- Auth: remplacer système simple par JWT ou OAuth; stocker mots de passe hashés (`password_hash`).
- Frameworks frontend: React/Vue/Svelte pour UI plus riche.
- Backend: utiliser un micro-framework PHP (Slim, Lumen) pour routage et middleware.
- DB: PostgreSQL si besoin d'ACID plus strict.

Questions pièges qu'un professeur pourrait poser:
- Pourquoi stocker les mots de passe en clair est dangereux ? (réponse: compromission, toujours hasher+salter)
- Comment sécuriser les endpoints ? (CSRF, validation côté serveur, prepared statements)
- Explique la différence entre session et token (stateful vs stateless)
- Montre comment ajouter pagination et recherche sur `courses`.

Notes pour l'examen:
- Si le prof demande un mot de passe, par défaut `admin@example.com` / `password` (changer en production).
