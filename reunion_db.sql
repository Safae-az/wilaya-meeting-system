-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : sam. 12 juil. 2025 à 18:05
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `reunion_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `admettre`
--

DROP TABLE IF EXISTS `admettre`;
CREATE TABLE IF NOT EXISTS `admettre` (
  `id_reunion` int NOT NULL,
  `id_point` int NOT NULL,
  PRIMARY KEY (`id_reunion`,`id_point`),
  KEY `id_point` (`id_point`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `admettre`
--

INSERT INTO `admettre` (`id_reunion`, `id_point`) VALUES
(1, 1),
(2, 2),
(3, 3);

-- --------------------------------------------------------

--
-- Structure de la table `compte_rendu`
--

DROP TABLE IF EXISTS `compte_rendu`;
CREATE TABLE IF NOT EXISTS `compte_rendu` (
  `id_compte_rendu` int NOT NULL AUTO_INCREMENT,
  `contenu` text NOT NULL,
  `date_validation` date DEFAULT NULL,
  `id_reunion` int DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  PRIMARY KEY (`id_compte_rendu`),
  UNIQUE KEY `id_reunion` (`id_reunion`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `compte_rendu`
--

INSERT INTO `compte_rendu` (`id_compte_rendu`, `contenu`, `date_validation`, `id_reunion`, `id_user`) VALUES
(1, 'Compte rendu de la réunion technique...', '2025-07-12', 1, 1),
(2, 'Discussion RH bien avancée...', '2025-07-12', 2, 3),
(3, 'Décisions prises pour le plan 2025...', '2025-07-12', 3, 4);

-- --------------------------------------------------------

--
-- Structure de la table `participer`
--

DROP TABLE IF EXISTS `participer`;
CREATE TABLE IF NOT EXISTS `participer` (
  `id_user` int NOT NULL,
  `id_reunion` int NOT NULL,
  PRIMARY KEY (`id_user`,`id_reunion`),
  KEY `id_reunion` (`id_reunion`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `participer`
--

INSERT INTO `participer` (`id_user`, `id_reunion`) VALUES
(1, 1),
(2, 1),
(3, 2),
(4, 3);

-- --------------------------------------------------------

--
-- Structure de la table `point_ordre_du_jour`
--

DROP TABLE IF EXISTS `point_ordre_du_jour`;
CREATE TABLE IF NOT EXISTS `point_ordre_du_jour` (
  `id_point` int NOT NULL AUTO_INCREMENT,
  `titre_point` varchar(100) NOT NULL,
  `description` text,
  `duree_estimee` time DEFAULT NULL,
  PRIMARY KEY (`id_point`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `point_ordre_du_jour`
--

INSERT INTO `point_ordre_du_jour` (`id_point`, `titre_point`, `description`, `duree_estimee`) VALUES
(1, 'Avancement des projets', 'Discussion sur l’état des projets en cours', '01:00:00'),
(2, 'Procédures de recrutement', 'Présentation des nouvelles procédures RH', '00:30:00'),
(3, 'Plan d’action 2025', 'Définir les objectifs pour 2025', '01:30:00');

-- --------------------------------------------------------

--
-- Structure de la table `reunion`
--

DROP TABLE IF EXISTS `reunion`;
CREATE TABLE IF NOT EXISTS `reunion` (
  `id_reunion` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(100) NOT NULL,
  `date_heure` datetime NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `id_service` int DEFAULT NULL,
  `id_salle` int DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  PRIMARY KEY (`id_reunion`),
  KEY `id_service` (`id_service`),
  KEY `id_salle` (`id_salle`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `reunion`
--

INSERT INTO `reunion` (`id_reunion`, `titre`, `date_heure`, `type`, `statut`, `id_service`, `id_salle`, `id_user`) VALUES
(1, 'Réunion Équipe Technique', '2025-07-12 09:00:00', 'Technique', 'Prévue', 1, 1, 1),
(2, 'Réunion RH', '2025-07-12 11:00:00', 'RH', 'Prévue', 2, 2, 3),
(3, 'Comité Stratégique', '2025-07-12 15:00:00', 'Stratégie', 'Prévue', 3, 3, 4);

-- --------------------------------------------------------

--
-- Structure de la table `salle`
--

DROP TABLE IF EXISTS `salle`;
CREATE TABLE IF NOT EXISTS `salle` (
  `id_salle` int NOT NULL AUTO_INCREMENT,
  `libelle_salle` varchar(100) NOT NULL,
  `capacite_salle` int NOT NULL,
  PRIMARY KEY (`id_salle`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `salle`
--

INSERT INTO `salle` (`id_salle`, `libelle_salle`, `capacite_salle`) VALUES
(1, 'Salle 201, Bâtiment Principal', 20),
(2, 'Salle 105', 15),
(3, 'Salle de Conférence', 30);

-- --------------------------------------------------------

--
-- Structure de la table `service`
--

DROP TABLE IF EXISTS `service`;
CREATE TABLE IF NOT EXISTS `service` (
  `id_service` int NOT NULL AUTO_INCREMENT,
  `nom_service` varchar(100) NOT NULL,
  PRIMARY KEY (`id_service`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `service`
--

INSERT INTO `service` (`id_service`, `nom_service`) VALUES
(1, 'Informatique'),
(2, 'Ressources Humaines'),
(3, 'Direction');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_pass` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `id_service` int DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`),
  KEY `id_service` (`id_service`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_user`, `nom`, `prenom`, `email`, `mot_pass`, `role`, `id_service`) VALUES
(1, 'Durand', 'Pierre', 'pierre.durand@example.com', 'pass123', 'Responsable IT', 1),
(2, 'Lemoine', 'Claire', 'claire.lemoine@example.com', 'pass123', 'Chef de projet', 1),
(3, 'Bernard', 'Julie', 'julie.bernard@example.com', 'pass123', 'DRH', 2),
(4, 'Dupont', 'Antoine', 'antoine.dupont@example.com', 'pass123', 'Directeur Général', 3);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
