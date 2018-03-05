-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le :  Dim 25 fév. 2018 à 22:26
-- Version du serveur :  10.1.22-MariaDB
-- Version de PHP :  7.1.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `projets4`
--

-- --------------------------------------------------------

--
-- Structure de la table `action`
--

CREATE TABLE `action` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `jouee` tinyint(1) NOT NULL,
  `cartes` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json_array)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `action`
--

INSERT INTO `action` (`id`, `nom`, `jouee`, `cartes`) VALUES
(1, 'secret', 0, NULL),
(2, 'compromis', 0, NULL),
(3, 'cadeau', 0, NULL),
(4, 'concurrence', 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `carte`
--

CREATE TABLE `carte` (
  `id` int(11) NOT NULL,
  `valeur` int(11) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `image_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `objectif` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `carte`
--

INSERT INTO `carte` (`id`, `valeur`, `updated_at`, `image_name`, `objectif`) VALUES
(1, 2, NULL, NULL, 1),
(2, 2, NULL, NULL, 1),
(3, 2, NULL, NULL, 2),
(4, 2, NULL, NULL, 2),
(5, 2, NULL, NULL, 3),
(6, 2, NULL, NULL, 3),
(7, 3, NULL, NULL, 4),
(8, 3, NULL, NULL, 4),
(9, 3, NULL, NULL, 4),
(10, 3, NULL, NULL, 5),
(11, 3, NULL, NULL, 5),
(12, 3, NULL, NULL, 5),
(13, 4, NULL, NULL, 6),
(14, 4, NULL, NULL, 6),
(15, 4, NULL, NULL, 6),
(16, 4, NULL, NULL, 6),
(17, 5, NULL, NULL, 7),
(18, 5, NULL, NULL, 7),
(19, 5, NULL, NULL, 7),
(20, 5, NULL, NULL, 7),
(21, 5, NULL, NULL, 7);

-- --------------------------------------------------------

--
-- Structure de la table `objectif`
--

CREATE TABLE `objectif` (
  `id` int(11) NOT NULL,
  `Valeur` int(11) NOT NULL,
  `image_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `objectif`
--

INSERT INTO `objectif` (`id`, `Valeur`, `image_name`, `updated_at`) VALUES
(1, 2, NULL, NULL),
(2, 2, NULL, NULL),
(3, 2, NULL, NULL),
(4, 3, NULL, NULL),
(5, 3, NULL, NULL),
(6, 4, NULL, NULL),
(7, 5, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `partie`
--

CREATE TABLE `partie` (
  `id` int(11) NOT NULL,
  `mainJ1` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json_array)',
  `mainJ2` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json_array)',
  `tourJoueurId` int(11) NOT NULL,
  `pioche` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json_array)',
  `created_at` datetime NOT NULL,
  `ended` tinyint(1) NOT NULL,
  `joueur1_id` int(11) DEFAULT NULL,
  `joueur2_id` int(11) DEFAULT NULL,
  `CartesJouees` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json_array)',
  `Objectifs` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json_array)',
  `PlateauJ1` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json_array)',
  `PlateauJ2` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json_array)',
  `Jetons` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)',
  `Actions` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_admin`
--

CREATE TABLE `user_admin` (
  `id` int(11) NOT NULL,
  `username` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `username_canonical` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `email_canonical` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `salt` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `confirmation_token` varchar(180) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `roles` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `image_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `user_admin`
--

INSERT INTO `user_admin` (`id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `confirmation_token`, `password_requested_at`, `roles`, `image_name`, `updated_at`) VALUES
(1, 'admin', 'admin', 'admin@mail.fr', 'admin@mail.fr', 1, NULL, '$2y$13$xMTxrlDP0krR9C.pG35AEec4LERvgrW7yfzpfxLtpqImXk6Mmvi2S', '2018-02-25 20:05:42', NULL, NULL, 'a:1:{i:0;s:10:\"ROLE_ADMIN\";}', NULL, '2018-02-25 20:07:56'),
(2, 'baggle', 'baggle', 'bagglestory@gmail.com', 'bagglestory@gmail.com', 1, NULL, '$2y$13$hbqOeg7sjlmUjgcFzvLqneiLODEG6/0fWAoILIjz/KdQbq9u8cIcq', '2018-02-15 15:57:31', NULL, NULL, 'a:0:{}', NULL, '2018-02-15 16:13:12');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `action`
--
ALTER TABLE `action`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `carte`
--
ALTER TABLE `carte`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `objectif`
--
ALTER TABLE `objectif`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `partie`
--
ALTER TABLE `partie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_59B1F3D92C1E237` (`joueur1_id`),
  ADD KEY `IDX_59B1F3D80744DD9` (`joueur2_id`);

--
-- Index pour la table `user_admin`
--
ALTER TABLE `user_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_6ACCF62E92FC23A8` (`username_canonical`),
  ADD UNIQUE KEY `UNIQ_6ACCF62EA0D96FBF` (`email_canonical`),
  ADD UNIQUE KEY `UNIQ_6ACCF62EC05FB297` (`confirmation_token`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `action`
--
ALTER TABLE `action`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT pour la table `carte`
--
ALTER TABLE `carte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT pour la table `objectif`
--
ALTER TABLE `objectif`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT pour la table `partie`
--
ALTER TABLE `partie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `user_admin`
--
ALTER TABLE `user_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `partie`
--
ALTER TABLE `partie`
  ADD CONSTRAINT `FK_59B1F3D80744DD9` FOREIGN KEY (`joueur2_id`) REFERENCES `user_admin` (`id`),
  ADD CONSTRAINT `FK_59B1F3D92C1E237` FOREIGN KEY (`joueur1_id`) REFERENCES `user_admin` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
