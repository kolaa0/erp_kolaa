-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 03 juin 2026 à 12:14
-- Version du serveur : 5.7.31
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `erp_kola`
--

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `ID_User` int(11) NOT NULL AUTO_INCREMENT,
  `Nom_Complet` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Mot_De_Passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Role` enum('ADMIN','EMPLOYE') COLLATE utf8mb4_unicode_ci DEFAULT 'EMPLOYE',
  `Actif` tinyint(1) DEFAULT '1',
  `Date_Creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `Derniere_Connexion` datetime DEFAULT NULL,
  PRIMARY KEY (`ID_User`),
  UNIQUE KEY `Email` (`Email`),
  KEY `idx_email` (`Email`),
  KEY `idx_role` (`Role`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`ID_User`, `Nom_Complet`, `Email`, `Mot_De_Passe`, `Role`, `Actif`, `Date_Creation`, `Derniere_Connexion`) VALUES
(1, 'Mamadou Ba', 'admin@kola.com', 'Kola', 'ADMIN', 1, '2026-06-03 02:31:50', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
