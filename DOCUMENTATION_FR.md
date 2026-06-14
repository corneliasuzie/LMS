# Documentation technique (FR)

Résumé:
- Frontend: HTML simple + CSS minimaliste (`assets/css/style.css`) + JS (`assets/js/app.js`) utilisant `fetch` pour appeler l'API.
- Backend: PHP avec `mysqli`, endpoints JSON dans `api/`.
- Base: MySQL (`lms_db`), migration dans `sql/migrate.sql`.

Installation et exécution (pas-à-pas pour débutant):
1. Démarre XAMPP (Apache + MySQL).
2. Place le dossier du projet dans `/opt/lampp/htdocs/lms`.
3. Importer la migration (sous XAMPP) :
```bash
sudo /opt/lampp/bin/mysql -u root -S /opt/lampp/var/mysql/mysql.sock < sql/migrate.sql
```
4. Ouvre `http://127.0.0.1/lms/` dans le navigateur.

Fichiers clés et leur rôle :
- `index.php` : page principale (SPA) — contient templates HTML et charge `assets/js/app.js`.
- `assets/css/style.css` : styles simples, modulaires et responsive.
- `assets/js/app.js` : logique frontend (login, lister cours, inscription, affichage utilisateur).
- `api/auth.php` : endpoints `login`, `logout`, `me` (JSON).
- `api/courses.php` : `GET` lister cours, `POST` créer cours (exemples).
- `api/enroll.php` : `POST` pour s'inscrire, `GET` pour lister les inscriptions d'un utilisateur.
- `sql/migrate.sql` : script SQL pour créer les tables et ajouter des données de démonstration.
- `config.php` : paramètres de connexion à la base et initialisation de la session.

Bonnes pratiques et améliorations simples (à montrer au prof) :
- Hachage des mots de passe : utiliser `password_hash()` à l'inscription et `password_verify()` à la connexion.
- Validation serveur : valider/assainir toutes les entrées, continuer d'utiliser `prepared statements`.
- Sécurité : protéger contre CSRF pour les formulaires sensibles et limiter les erreurs détaillées en production.
- Passage à une API REST plus robuste : ajouter contrôles, codes HTTP, pagination (`LIMIT/OFFSET`) et recherche.

Alternatives technologiques (si tu veux changer plus tard) :
- Frontend : React / Vue / Svelte pour UI plus riche et composants réutilisables.
- Backend : Slim ou Lumen (micro-frameworks PHP) pour routage clair.
- DB : PostgreSQL pour fonctionnalités avancées (ex : gestion JSON, contraintes plus strictes).

Questions pièges possibles (prépare tes réponses) :
- Pourquoi ne stocke-t-on jamais un mot de passe en clair ?
- Explique une injection SQL et comment s'en protéger.
- Quelle est la différence entre une session (PHP) et un token JWT ?
- Comment ajouter une pagination et recherche sur la liste des cours ?

Conseils pour l'examen/présentation :
- Montre le flux : frontend (`index.php` + `app.js`) → appel `fetch` → `api/*.php` → MySQL.
- Mets en avant la simplicité du code : chaque fichier a une responsabilité claire.
- Si on te demande une amélioration : propose immédiatement `password_hash`, page admin et upload sécurisé.

Comptes de démonstration :
- admin@example.com / password
- student@example.com / password

Fin.
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
