-- ============================================
-- MIGRATIONS - Création des tables
-- Projet Web 4 All
-- ============================================

-- Table Permissions
CREATE TABLE IF NOT EXISTS Permissions (
    niveau_permission INTEGER PRIMARY KEY,
    role VARCHAR(50) NOT NULL
);

-- Table Compte
CREATE TABLE IF NOT EXISTS Compte (
    id_compte INTEGER PRIMARY KEY,
    email VARCHAR(50) NOT NULL UNIQUE,
    mot_de_passe TEXT NOT NULL,
    niveau_permission INTEGER NOT NULL,
    role VARCHAR(50),
    FOREIGN KEY (niveau_permission) REFERENCES Permissions(niveau_permission)
);

-- Table Ville
CREATE TABLE IF NOT EXISTS Ville (
    id_ville INTEGER PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    code_postal INTEGER NOT NULL
);

-- Table Adresse
CREATE TABLE IF NOT EXISTS Adresse (
    id_adresse INTEGER PRIMARY KEY,
    numero_rue VARCHAR(280),
    nom_rue VARCHAR(50),
    id_ville INTEGER NOT NULL,
    FOREIGN KEY (id_ville) REFERENCES Ville(id_ville)
);

-- Table Entreprise
CREATE TABLE IF NOT EXISTS Entreprise (
    id_entreprise INTEGER PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    description VARCHAR(280),
    email VARCHAR(25),
    telephone VARCHAR(10),
    domaine VARCHAR(50),
    adresse VARCHAR(100),
    id_compte INTEGER,
    id_pilote INTEGER,
    id_ville INTEGER,
    FOREIGN KEY (id_compte) REFERENCES Compte(id_compte),
    FOREIGN KEY (id_ville) REFERENCES Ville(id_ville)
);

-- Table Pilote (hérite de Compte)
CREATE TABLE IF NOT EXISTS Pilote (
    id_compte INTEGER NOT NULL,
    id_pilote INTEGER PRIMARY KEY,
    nom VARCHAR(30),
    prenom VARCHAR(50),
    email_publique VARCHAR(80),
    niveau_permission INTEGER,
    role VARCHAR(50),
    email VARCHAR(50),
    mot_de_passe TEXT,
    FOREIGN KEY (id_compte) REFERENCES Compte(id_compte)
);

-- Table Etudiant (hérite de Compte)
CREATE TABLE IF NOT EXISTS Etudiant (
    id_compte INTEGER NOT NULL,
    id_etudiant INTEGER PRIMARY KEY,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    email_publique VARCHAR(50),
    niveau_permission INTEGER,
    role VARCHAR(50),
    email VARCHAR(50),
    mot_de_passe TEXT,
    id_compte_pilote INTEGER,
    id_pilote INTEGER,
    FOREIGN KEY (id_compte) REFERENCES Compte(id_compte),
    FOREIGN KEY (id_pilote) REFERENCES Pilote(id_pilote)
);

-- Table Competences
CREATE TABLE IF NOT EXISTS Competences (
    id_competences INTEGER PRIMARY KEY,
    nom VARCHAR(50) NOT NULL
);

-- Table Offre
CREATE TABLE IF NOT EXISTS Offre (
    id_offre INTEGER PRIMARY KEY,
    titre VARCHAR(50) NOT NULL,
    description TEXT,
    base_remuneration VARCHAR(50),
    date DATE,
    id_entreprise_appartient INTEGER,
    FOREIGN KEY (id_entreprise_appartient) REFERENCES Entreprise(id_entreprise)
);

-- Table possede (relation Offre <-> Competences)
CREATE TABLE IF NOT EXISTS possede (
    id_competences INTEGER NOT NULL,
    id_offre INTEGER NOT NULL,
    PRIMARY KEY (id_competences, id_offre),
    FOREIGN KEY (id_competences) REFERENCES Competences(id_competences),
    FOREIGN KEY (id_offre) REFERENCES Offre(id_offre)
);

-- Table candidate (relation Etudiant <-> Offre)
CREATE TABLE IF NOT EXISTS candidate (
    id_offre INTEGER NOT NULL,
    id_compte INTEGER NOT NULL,
    id_etudiant INTEGER NOT NULL,
    id_candidature INTEGER,
    CV VARCHAR(100),
    lettre_motivation VARCHAR(100),
    PRIMARY KEY (id_offre, id_compte, id_etudiant),
    FOREIGN KEY (id_offre) REFERENCES Offre(id_offre),
    FOREIGN KEY (id_etudiant) REFERENCES Etudiant(id_etudiant)
);

-- Table peut_wishlist (relation Etudiant <-> Offre)
CREATE TABLE IF NOT EXISTS peut_wishlist (
    id_offre_peut_wishlist INTEGER NOT NULL,
    id_compte_etudiant INTEGER NOT NULL,
    id_etudiant_etudiant INTEGER NOT NULL,
    id_wishlist INTEGER,
    id_etudiant INTEGER,
    id_offre INTEGER,
    PRIMARY KEY (id_offre_peut_wishlist, id_compte_etudiant, id_etudiant_etudiant),
    FOREIGN KEY (id_offre_peut_wishlist) REFERENCES Offre(id_offre),
    FOREIGN KEY (id_etudiant_etudiant) REFERENCES Etudiant(id_etudiant)
);

-- Table note (relation Entreprise <-> Etudiant)
CREATE TABLE IF NOT EXISTS note (
    id_entreprise INTEGER NOT NULL,
    id_notation INTEGER,
    id_etudiant INTEGER NOT NULL,
    notation SMALLINT CHECK (notation BETWEEN 0 AND 5),
    commentaire VARCHAR(280),
    nom VARCHAR(50),
    prenom VARCHAR(50),
    PRIMARY KEY (id_entreprise, id_etudiant),
    FOREIGN KEY (id_entreprise) REFERENCES Entreprise(id_entreprise),
    FOREIGN KEY (id_etudiant) REFERENCES Etudiant(id_etudiant)
);
