-- ============================================
-- Script SQL pour créer la base de données LMS
-- Étudiant : L2 Informatique, Université de Yaoundé 1
-- ============================================

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS lms_db CHARACTER SET utf8 COLLATE utf8_general_ci;

-- Utiliser cette base de données
USE lms_db;

-- -----------------------------------------------
-- Table des utilisateurs (enseignants, étudiants, admin)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'enseignant', 'etudiant') NOT NULL DEFAULT 'etudiant',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------
-- Table des modules (créés par le promoteur/admin)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    admin_id INT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- -----------------------------------------------
-- Table des cours (créés par les enseignants, liés à un module)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS cours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    enseignant_id INT NOT NULL,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    FOREIGN KEY (enseignant_id) REFERENCES users(id) ON DELETE CASCADE
);

-- -----------------------------------------------
-- Table des leçons (chaque cours peut avoir plusieurs leçons)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS lecons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cours_id INT NOT NULL,
    titre VARCHAR(200) NOT NULL,
    type_contenu ENUM('pdf', 'video') NOT NULL,
    fichier VARCHAR(255) NOT NULL,
    ordre INT DEFAULT 1,
    FOREIGN KEY (cours_id) REFERENCES cours(id) ON DELETE CASCADE
);

-- -----------------------------------------------
-- Table des évaluations (chaque leçon a une évaluation)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lecon_id INT NOT NULL,
    titre VARCHAR(200) NOT NULL,
    FOREIGN KEY (lecon_id) REFERENCES lecons(id) ON DELETE CASCADE
);

-- -----------------------------------------------
-- Table des questions (chaque évaluation a plusieurs questions)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluation_id INT NOT NULL,
    question TEXT NOT NULL,
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE
);

-- -----------------------------------------------
-- Table des réponses (chaque question a plusieurs réponses, une seule correcte)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS reponses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    texte_reponse VARCHAR(255) NOT NULL,
    est_correcte TINYINT(1) DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- -----------------------------------------------
-- Table de progression des étudiants
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS progression (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    lecon_id INT NOT NULL,
    note DECIMAL(5,2) DEFAULT 0,
    completee TINYINT(1) DEFAULT 0,
    date_completion DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_progression (etudiant_id, lecon_id),
    FOREIGN KEY (etudiant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lecon_id) REFERENCES lecons(id) ON DELETE CASCADE
);

-- -----------------------------------------------
-- Table des certificats attribués par l'admin
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS certificats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT NOT NULL,
    module_id INT NOT NULL,
    date_attribution DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_certificat (etudiant_id, module_id),
    FOREIGN KEY (etudiant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
);

-- -----------------------------------------------
-- Données de test : un admin par défaut
-- Mot de passe : admin123 (hashé avec MD5 pour la simplicité)
-- -----------------------------------------------
INSERT INTO users (nom, email, mot_de_passe, role) VALUES
('Administrateur', 'admin@lms.com', MD5('admin123'), 'admin'),
('Prof Messi', 'prof@lms.com', MD5('prof123'), 'enseignant'),
('Marie Etudiante', 'marie@lms.com', MD5('marie123'), 'etudiant');
