-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 17 juil. 2026 à 16:11
-- Version du serveur : 8.4.7
-- Version de PHP : 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `library_manager`
--

-- --------------------------------------------------------

--
-- Structure de la table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `admins`
--

INSERT INTO `admins` (`id`, `nom`, `email`, `mot_de_passe`) VALUES
(1, 'Massi', 'massi@gmail.com', '$2y$10$1xRRQL30BHFsh6GCBhNgHeBr5tbgOOoKpTckUgA2kAMGXZsys4pXa'),
(2, 'Sara Med', 'saramed@gmail.com', '$2y$10$70x2tNSr2k1/Ef8Bp/wnKuHflWR80/bX42rOc7n3Kj7aMMKwEqb0K');

-- --------------------------------------------------------

--
-- Structure de la table `books`
--

DROP TABLE IF EXISTS `books`;
CREATE TABLE IF NOT EXISTS `books` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auteur` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `annee` int DEFAULT NULL,
  `disponible` tinyint(1) DEFAULT '1',
  `id_category` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_category` (`id_category`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `books`
--

INSERT INTO `books` (`id`, `titre`, `auteur`, `annee`, `disponible`, `id_category`) VALUES
(1, 'Apprendre PHP', 'Jean Dupont', 2023, 1, 1),
(2, 'Algèbre Linéaire', 'Ahmed Salem', 2021, 1, 2),
(3, 'Histoire de Mauritanie', 'Mohamed Ould', 2020, 1, 3),
(4, 'c++', 'Maro', 1994, 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `borrowers`
--

DROP TABLE IF EXISTS `borrowers`;
CREATE TABLE IF NOT EXISTS `borrowers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `borrowers`
--

INSERT INTO `borrowers` (`id`, `nom`, `email`, `telephone`, `date_creation`) VALUES
(1, 'Ahmed Mohamed', 'ahmed@example.com', '22000001', '2026-07-14 06:53:51'),
(2, 'Fatimetou Salem', 'fatimetou@example.com', '22000002', '2026-07-14 06:53:51'),
(3, 'Mohamed Ali', 'mohamed@example.com', '22000004', '2026-07-14 06:53:51');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `nom`) VALUES
(1, 'Programmation'),
(2, 'Mathématiques'),
(3, 'Histoire'),
(5, 'Statistique');

-- --------------------------------------------------------

--
-- Structure de la table `loans`
--

DROP TABLE IF EXISTS `loans`;
CREATE TABLE IF NOT EXISTS `loans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_book` int NOT NULL,
  `id_borrower` int NOT NULL,
  `date_emprunt` date NOT NULL,
  `date_retour_prevue` date NOT NULL,
  `date_retour_reelle` date DEFAULT NULL,
  `statut` enum('en_cours','retourne') COLLATE utf8mb4_unicode_ci DEFAULT 'en_cours',
  PRIMARY KEY (`id`),
  KEY `fk_loan_book` (`id_book`),
  KEY `fk_loan_borrower` (`id_borrower`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `loans`
--

INSERT INTO `loans` (`id`, `id_book`, `id_borrower`, `date_emprunt`, `date_retour_prevue`, `date_retour_reelle`, `statut`) VALUES
(1, 1, 1, '2026-07-14', '2026-07-28', '2026-07-14', 'retourne');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
