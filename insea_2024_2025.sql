-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 21 avr. 2025 à 14:32
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
-- Base de données : `insea_2024_2025`
--

-- --------------------------------------------------------

--
-- Structure de la table `annee_sco`
--

DROP TABLE IF EXISTS `annee_sco`;
CREATE TABLE IF NOT EXISTS `annee_sco` (
  `Id_AS` int NOT NULL AUTO_INCREMENT,
  `Lib_AS` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`Id_AS`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `annee_sco`
--

INSERT INTO `annee_sco` (`Id_AS`, `Lib_AS`) VALUES
(1, '2024_2025'),
(2, '2023_2024'),
(3, '2022_2023'),
(4, '2021_2022');

-- --------------------------------------------------------

--
-- Structure de la table `etudiants`
--

DROP TABLE IF EXISTS `etudiants`;
CREATE TABLE IF NOT EXISTS `etudiants` (
  `ID_Etud` int NOT NULL AUTO_INCREMENT,
  `Nom_Etud` varchar(20) NOT NULL,
  `Prenom_Etud` varchar(20) NOT NULL,
  `Matricule_Etud` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `Email_Etud` varchar(100) NOT NULL,
  `Login_Etud` varchar(30) NOT NULL,
  `MP_Etud` varchar(255) NOT NULL,
  `Role_Etud` varchar(10) DEFAULT NULL,
  `Sexe_Etud` varchar(10) DEFAULT NULL,
  `Fil_Etud` int NOT NULL,
  `Niv_Etud` int NOT NULL,
  `AS_Etud` int NOT NULL,
  `Photo_Etud` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  PRIMARY KEY (`ID_Etud`),
  UNIQUE KEY `Email_Etud` (`Email_Etud`),
  UNIQUE KEY `Login_Etud` (`Login_Etud`),
  UNIQUE KEY `Matricule_Etud` (`Matricule_Etud`),
  KEY `fk_etudiants_niveaux` (`Niv_Etud`),
  KEY `fk_etudiants_annee_sco` (`AS_Etud`),
  KEY `fk_etudiants_filiere` (`Fil_Etud`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `etudiants`
--

INSERT INTO `etudiants` (`ID_Etud`, `Nom_Etud`, `Prenom_Etud`, `Matricule_Etud`, `Email_Etud`, `Login_Etud`, `MP_Etud`, `Role_Etud`, `Sexe_Etud`, `Fil_Etud`, `Niv_Etud`, `AS_Etud`, `Photo_Etud`) VALUES
(4, 'Jin Woo', 'Sung', '+ss999', 'sungjinwoo@solo.leveling', 'sung', 'sung', NULL, 'M', 2, 3, 4, 'SungJinWoo.jpg'),
(5, 'Hae In', 'Cha', 'S151', 'chahaein@solo.leveling', 'hunterChan', 'hunterChan', NULL, 'F', 2, 1, 4, 'ChaHaeIn.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `filiere`
--

DROP TABLE IF EXISTS `filiere`;
CREATE TABLE IF NOT EXISTS `filiere` (
  `Id_Fil` int NOT NULL AUTO_INCREMENT,
  `Lib_Fil` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Abr_Fil` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`Id_Fil`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `filiere`
--

INSERT INTO `filiere` (`Id_Fil`, `Lib_Fil`, `Abr_Fil`) VALUES
(1, 'Master Systèmes d\'Information et Systèmes Intelligents', 'M2SI'),
(2, 'CI DATA & SOFTWARE ENGINEERING', 'DSE');

-- --------------------------------------------------------

--
-- Structure de la table `niveaux`
--

DROP TABLE IF EXISTS `niveaux`;
CREATE TABLE IF NOT EXISTS `niveaux` (
  `Id_Niv` int NOT NULL AUTO_INCREMENT,
  `Lib_Niv` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Abr_Niv` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`Id_Niv`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `niveaux`
--

INSERT INTO `niveaux` (`Id_Niv`, `Lib_Niv`, `Abr_Niv`) VALUES
(1, '1ère année', 'A1'),
(2, '2e année', 'A2'),
(3, '3e année', 'A3');

-- --------------------------------------------------------

--
-- Structure de la table `qcm`
--

DROP TABLE IF EXISTS `qcm`;
CREATE TABLE IF NOT EXISTS `qcm` (
  `qcm_id` int NOT NULL AUTO_INCREMENT,
  `titre_qcm` varchar(100) DEFAULT NULL,
  `niveau_qcm` int NOT NULL,
  `id_filiere` int NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`qcm_id`),
  KEY `fk_qcm_filiere` (`id_filiere`),
  KEY `fk_qcm_niveaux` (`niveau_qcm`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `qcm`
--

INSERT INTO `qcm` (`qcm_id`, `titre_qcm`, `niveau_qcm`, `id_filiere`, `date_creation`) VALUES
(1, 'MathematiqueS', 2, 1, '2025-04-14 18:04:56'),
(2, 'Java POO', 1, 2, '2025-04-14 21:05:56');

-- --------------------------------------------------------

--
-- Structure de la table `questions`
--

DROP TABLE IF EXISTS `questions`;
CREATE TABLE IF NOT EXISTS `questions` (
  `question_id` int NOT NULL AUTO_INCREMENT,
  `question_text` text,
  `commentaire` text,
  `qcm_id` int DEFAULT NULL,
  PRIMARY KEY (`question_id`),
  KEY `fk_questions_qcm` (`qcm_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `questions`
--

INSERT INTO `questions` (`question_id`, `question_text`, `commentaire`, `qcm_id`) VALUES
(2, 'comment afficher un message en Java', 'choisir la réponse juste', 2),
(18, 'Lequel des éléments suivants n’est pas un concept POO en Java', '', 2),
(19, 'Quand la surcharge de méthode est-elle déterminée', '', 2),
(20, 'Quand la surcharge ne se produit pas', '', 2),
(21, 'Quel concept de Java est un moyen de convertir des objets du monde réel en termes de classe', '', 2),
(22, 'Quel concept de Java est utilisé en combinant des méthodes et des attributs dans une classe', '', 2),
(23, 'Comment ça s’appelle si un objet a son propre cycle de vie', '', 2),
(24, 'Comment s’appelle-t-on dans le cas où l’objet d’une classe mère est détruit donc l’objet d’une classe fille sera détruit également', '', 2),
(25, 'Comment s’appelle-t-on l’objet a son propre cycle de vie et l’objet d’une classe fille ne dépend pas à un autre objet d’une classe mère', '', 2),
(26, 'Quels keywords sont utilisés pour spécifier la visibilité des propriétés et des méthodes', '', 2);

-- --------------------------------------------------------

--
-- Structure de la table `reponses`
--

DROP TABLE IF EXISTS `reponses`;
CREATE TABLE IF NOT EXISTS `reponses` (
  `reponse_id` int NOT NULL AUTO_INCREMENT,
  `reponse_text` text,
  `est_juste` tinyint(1) DEFAULT NULL,
  `question_id` int DEFAULT NULL,
  PRIMARY KEY (`reponse_id`),
  KEY `fk_reponses_questions` (`question_id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `reponses`
--

INSERT INTO `reponses` (`reponse_id`, `reponse_text`, `est_juste`, `question_id`) VALUES
(1, 'system.in.print()', 0, 2),
(2, 'system.in.println()', 0, 2),
(3, 'system.out.println()', 1, 2),
(4, 'system.out.print()', 0, 2),
(65, 'Héritage', 0, 18),
(66, 'Encapsulation', 0, 18),
(67, 'Polymorphisme', 0, 18),
(68, 'Compilation', 1, 18),
(69, 'Au moment de l’exécution', 0, 19),
(70, 'Au moment de la compilation', 1, 19),
(71, 'Au moment du codage', 0, 19),
(72, 'Au moment de l’exécution', 0, 19),
(73, 'Quand il y a plusieurs méthodes avec le même nom mais avec une signature de méthode différente et un nombre ou un type de paramètres différent', 0, 20),
(74, 'Quand il y a plusieurs méthodes avec le même nom, le même nombre de paramètres et le type mais une signature différente', 1, 20),
(75, 'Quand il y a plusieurs méthodes avec le même nom, la même signature, le même nombre de paramètres mais un type différent', 0, 20),
(76, 'Quand il y a plusieurs méthodes avec le même nom, la même signature mais avec différente signature', 0, 20),
(77, 'Polymorphisme', 0, 21),
(78, 'Encapsulation', 0, 21),
(79, 'Abstraction', 1, 21),
(80, 'Héritage', 0, 21),
(81, 'Polymorphisme', 0, 22),
(82, 'Encapsulation', 1, 22),
(83, 'Abstraction', 0, 22),
(84, 'Héritage', 0, 22),
(85, 'Agrégation', 0, 23),
(86, 'Composition', 0, 23),
(87, 'Encapsulation', 0, 23),
(88, 'Association', 1, 23),
(89, 'Agrégation', 0, 24),
(90, 'Composition', 1, 24),
(91, 'Encapsulation', 0, 24),
(92, 'Association', 0, 24),
(93, 'Agrégation', 1, 25),
(94, 'Composition', 0, 25),
(95, 'Encapsulation', 0, 25),
(96, 'Association', 0, 25),
(97, 'final', 0, 26),
(98, 'abstract', 0, 26),
(99, 'public', 1, 26),
(100, 'constatant', 0, 26);

-- --------------------------------------------------------

--
-- Structure de la table `resultat_qcm`
--

DROP TABLE IF EXISTS `resultat_qcm`;
CREATE TABLE IF NOT EXISTS `resultat_qcm` (
  `resultat_id` int NOT NULL AUTO_INCREMENT,
  `id_etudiant` int DEFAULT NULL,
  `id_qcm` int DEFAULT NULL,
  `note` float DEFAULT NULL,
  `date_passage` datetime DEFAULT CURRENT_TIMESTAMP,
  `duree` int DEFAULT NULL,
  PRIMARY KEY (`resultat_id`),
  KEY `fk_resultat_etudiant` (`id_etudiant`),
  KEY `fk_resultat_qcm` (`id_qcm`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `resultat_qcm`
--

INSERT INTO `resultat_qcm` (`resultat_id`, `id_etudiant`, `id_qcm`, `note`, `date_passage`, `duree`) VALUES
(8, 5, 2, 11, '2025-04-21 15:07:10', 80);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `etudiants`
--
ALTER TABLE `etudiants`
  ADD CONSTRAINT `fk_etudiants_annee_sco` FOREIGN KEY (`AS_Etud`) REFERENCES `annee_sco` (`Id_AS`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_etudiants_filiere` FOREIGN KEY (`Fil_Etud`) REFERENCES `filiere` (`Id_Fil`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_etudiants_niveaux` FOREIGN KEY (`Niv_Etud`) REFERENCES `niveaux` (`Id_Niv`) ON DELETE CASCADE;

--
-- Contraintes pour la table `qcm`
--
ALTER TABLE `qcm`
  ADD CONSTRAINT `fk_qcm_filiere` FOREIGN KEY (`id_filiere`) REFERENCES `filiere` (`Id_Fil`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_qcm_niveaux` FOREIGN KEY (`niveau_qcm`) REFERENCES `niveaux` (`Id_Niv`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Contraintes pour la table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `fk_questions_qcm` FOREIGN KEY (`qcm_id`) REFERENCES `qcm` (`qcm_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reponses`
--
ALTER TABLE `reponses`
  ADD CONSTRAINT `fk_reponses_questions` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `resultat_qcm`
--
ALTER TABLE `resultat_qcm`
  ADD CONSTRAINT `fk_resultat_etudiant` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiants` (`ID_Etud`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_resultat_qcm` FOREIGN KEY (`id_qcm`) REFERENCES `qcm` (`qcm_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
