-- ============================================
-- SEED - Données de test
-- Projet Web 4 All
-- ============================================

-- Permissions
INSERT INTO Permissions (niveau_permission, role) VALUES
(1, 'etudiant'),
(2, 'pilote'),
(3, 'admin');

-- Comptes
INSERT INTO Compte (id_compte, email, mot_de_passe, niveau_permission, role) VALUES
(1, 'admin@web4all.fr', 'hashed_password_admin', 3, 'admin'),
(2, 'pilote1@web4all.fr', 'hashed_password_pilote1', 2, 'pilote'),
(3, 'pilote2@web4all.fr', 'hashed_password_pilote2', 2, 'pilote'),
(4, 'etudiant1@web4all.fr', 'hashed_password_etu1', 1, 'etudiant'),
(5, 'etudiant2@web4all.fr', 'hashed_password_etu2', 1, 'etudiant'),
(6, 'etudiant3@web4all.fr', 'hashed_password_etu3', 1, 'etudiant'),
(7, 'contact@google.fr', 'hashed_password_google', 1, 'entreprise'),
(8, 'contact@airbus.fr', 'hashed_password_airbus', 1, 'entreprise'),
(9, 'contact@carrefour.fr', 'hashed_password_carrefour', 1, 'entreprise');

-- Villes
INSERT INTO Ville (id_ville, nom, code_postal) VALUES
(1, 'Paris', 75000),
(2, 'Toulouse', 31000),
(3, 'Lyon', 69000),
(4, 'Bordeaux', 33000),
(5, 'Lille', 59000);

-- Adresses
INSERT INTO Adresse (id_adresse, numero_rue, nom_rue, id_ville) VALUES
(1, '8', 'Rue de Londres', 1),
(2, '316', 'Route de Bayonne', 2),
(3, '25', 'Rue de la République', 3),
(4, '10', 'Cours de l Intendance', 4),
(5, '50', 'Rue Nationale', 5);

-- Pilotes
INSERT INTO Pilote (id_compte, id_pilote, nom, prenom, email_publique, niveau_permission, role, email, mot_de_passe) VALUES
(2, 1, 'Dupont', 'Jean', 'jean.dupont@web4all.fr', 2, 'pilote', 'pilote1@web4all.fr', 'hashed_password_pilote1'),
(3, 2, 'Martin', 'Sophie', 'sophie.martin@web4all.fr', 2, 'pilote', 'pilote2@web4all.fr', 'hashed_password_pilote2');

-- Entreprises
INSERT INTO Entreprise (id_entreprise, nom, description, email, telephone, domaine, adresse, id_compte, id_pilote, id_ville) VALUES
(1, 'Google France', 'Filiale française de Google, spécialisée en solutions cloud et IA', 'contact@google.fr', '0102030405', 'Tech', '8 Rue de Londres', 7, 1, 1),
(2, 'Airbus', 'Leader européen de l aéronautique et de la défense', 'contact@airbus.fr', '0561000000', 'Aéronautique', '316 Route de Bayonne', 8, 1, 2),
(3, 'Carrefour', 'Groupe de distribution alimentaire et non-alimentaire', 'contact@carrefour.fr', '0169930000', 'Distribution', '25 Rue de la République', 9, 2, 3);

-- Etudiants
INSERT INTO Etudiant (id_compte, id_etudiant, nom, prenom, email_publique, niveau_permission, role, email, mot_de_passe, id_compte_pilote, id_pilote) VALUES
(4, 1, 'Leroy', 'Alice', 'alice.leroy@etudiant.fr', 1, 'etudiant', 'etudiant1@web4all.fr', 'hashed_password_etu1', 2, 1),
(5, 2, 'Bernard', 'Thomas', 'thomas.bernard@etudiant.fr', 1, 'etudiant', 'etudiant2@web4all.fr', 'hashed_password_etu2', 2, 1),
(6, 3, 'Petit', 'Emma', 'emma.petit@etudiant.fr', 1, 'etudiant', 'etudiant3@web4all.fr', 'hashed_password_etu3', 3, 2);

-- Compétences
INSERT INTO Competences (id_competences, nom) VALUES
(1, 'PHP'),
(2, 'JavaScript'),
(3, 'Python'),
(4, 'SQL'),
(5, 'React'),
(6, 'Docker'),
(7, 'Git'),
(8, 'Machine Learning');

-- Offres
INSERT INTO Offre (id_offre, titre, description, base_remuneration, date, id_entreprise_appartient) VALUES
(1, 'Développeur PHP Full-Stack', 'Stage de 6 mois en développement web PHP/Twig', '600€/mois', '2024-09-01', 1),
(2, 'Data Engineer', 'Stage en ingénierie des données et pipelines ML', '800€/mois', '2024-09-01', 1),
(3, 'Développeur Systèmes Embarqués', 'Stage en développement logiciel pour systèmes avioniques', '700€/mois', '2024-06-01', 2),
(4, 'Développeur Python', 'Stage en automatisation et scripting Python', '650€/mois', '2024-07-01', 2),
(5, 'Développeur E-commerce', 'Stage en développement de la plateforme e-commerce', '550€/mois', '2024-09-01', 3);

-- Possède (Offre <-> Compétences)
INSERT INTO possede (id_competences, id_offre) VALUES
(1, 1), -- PHP -> Offre PHP Full-Stack
(2, 1), -- JavaScript -> Offre PHP Full-Stack
(4, 1), -- SQL -> Offre PHP Full-Stack
(7, 1), -- Git -> Offre PHP Full-Stack
(3, 2), -- Python -> Offre Data Engineer
(8, 2), -- ML -> Offre Data Engineer
(4, 2), -- SQL -> Offre Data Engineer
(6, 3), -- Docker -> Offre Systèmes Embarqués
(7, 3), -- Git -> Offre Systèmes Embarqués
(3, 4), -- Python -> Offre Développeur Python
(7, 4), -- Git -> Offre Développeur Python
(2, 5), -- JavaScript -> Offre E-commerce
(5, 5); -- React -> Offre E-commerce

-- Candidatures
INSERT INTO candidate (id_offre, id_compte, id_etudiant, id_candidature, CV, lettre_motivation) VALUES
(1, 4, 1, 1, 'cv_alice_leroy.pdf', 'motivation_alice_leroy.pdf'),
(2, 4, 1, 2, 'cv_alice_leroy.pdf', 'motivation_alice_data.pdf'),
(3, 5, 2, 3, 'cv_thomas_bernard.pdf', 'motivation_thomas_airbus.pdf'),
(5, 6, 3, 4, 'cv_emma_petit.pdf', 'motivation_emma_carrefour.pdf');

-- Wishlist
INSERT INTO peut_wishlist (id_offre_peut_wishlist, id_compte_etudiant, id_etudiant_etudiant, id_wishlist, id_etudiant, id_offre) VALUES
(1, 4, 1, 1, 1, 1),
(2, 4, 1, 2, 1, 2),
(3, 5, 2, 3, 2, 3),
(4, 5, 2, 4, 2, 4),
(5, 6, 3, 5, 3, 5);

-- Notes
INSERT INTO note (id_entreprise, id_notation, id_etudiant, notation, commentaire, nom, prenom) VALUES
(1, 1, 1, 5, 'Excellente entreprise, très formatrice et bonne ambiance', 'Leroy', 'Alice'),
(2, 2, 2, 4, 'Bonne expérience, projets très techniques et stimulants', 'Bernard', 'Thomas'),
(3, 3, 3, 3, 'Expérience correcte, manque un peu d encadrement', 'Petit', 'Emma');
