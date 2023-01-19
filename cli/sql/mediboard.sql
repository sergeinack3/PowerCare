-- @package Mediboard
-- @author SARL OpenXtrem
-- @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 


--
-- Structure de la table `access_log`
--

CREATE TABLE IF NOT EXISTS `access_log` (
  `accesslog_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `period` datetime NOT NULL,
  `hits` int(11) unsigned NOT NULL DEFAULT '0',
  `duration` float NOT NULL DEFAULT '0',
  `request` float NOT NULL DEFAULT '0',
  `size` int(11) unsigned DEFAULT NULL,
  `errors` int(11) unsigned DEFAULT NULL,
  `warnings` int(11) unsigned DEFAULT NULL,
  `notices` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`accesslog_id`),
  UNIQUE KEY `triplet` (`module`,`action`,`period`),
  KEY `module` (`module`),
  KEY `action` (`action`)
) /*! ENGINE=MyISAM */  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `etab_externe`
--

CREATE TABLE IF NOT EXISTS `etab_externe` (
  `etab_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `raison_sociale` varchar(50) DEFAULT NULL,
  `adresse` text,
  `cp` int(5) unsigned zerofill DEFAULT NULL,
  `ville` varchar(50) DEFAULT NULL,
  `tel` bigint(10) unsigned zerofill DEFAULT NULL,
  `fax` bigint(10) unsigned zerofill DEFAULT NULL,
  `finess` int(9) unsigned zerofill DEFAULT NULL,
  `siret` char(14) DEFAULT NULL,
  `ape` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`etab_id`)
) /*! ENGINE=MyISAM */ DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `groups_mediboard`
--

CREATE TABLE IF NOT EXISTS `groups_mediboard` (
  `group_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `text` varchar(255) NOT NULL,
  `raison_sociale` varchar(50) DEFAULT NULL,
  `adresse` text,
  `cp` int(5) unsigned zerofill DEFAULT NULL,
  `ville` varchar(50) DEFAULT NULL,
  `tel` bigint(10) unsigned zerofill DEFAULT NULL,
  `fax` bigint(10) unsigned zerofill DEFAULT NULL,
  `directeur` varchar(50) DEFAULT NULL,
  `domiciliation` varchar(9) DEFAULT NULL,
  `siret` varchar(14) DEFAULT NULL,
  `ape` varchar(6) DEFAULT NULL,
  `mail` varchar(50) DEFAULT NULL,
  `web` varchar(255) DEFAULT NULL,
  `tel_anesth` bigint(10) unsigned zerofill DEFAULT NULL,
  `service_urgences_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`group_id`)
) /*! ENGINE=MyISAM */  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Contenu de la table `groups_mediboard`
--

INSERT INTO `groups_mediboard` (`group_id`, `text`, `raison_sociale`, `adresse`, `cp`, `ville`, `tel`, `fax`, `directeur`, `domiciliation`, `siret`, `ape`, `mail`, `web`, `tel_anesth`, `service_urgences_id`) VALUES
(1, 'Etablissement', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE IF NOT EXISTS `message` (
  `message_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `deb` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `fin` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `titre` varchar(40) NOT NULL DEFAULT '',
  `corps` text NOT NULL,
  `urgence` enum('normal','urgent') NOT NULL DEFAULT 'normal',
  `module_id` int(11) unsigned DEFAULT NULL,
  `group_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`message_id`),
  INDEX `group_id` (`group_id`)
) /*! ENGINE=MyISAM */ DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `modules`
--

CREATE TABLE IF NOT EXISTS `modules` (
  `mod_id` int(11) NOT NULL AUTO_INCREMENT,
  `mod_name` varchar(64) NOT NULL DEFAULT '',
  `mod_directory` varchar(64) NOT NULL DEFAULT '',
  `mod_version` varchar(10) NOT NULL DEFAULT '',
  `mod_setup_class` varchar(64) NOT NULL DEFAULT '',
  `mod_type` varchar(64) NOT NULL DEFAULT '',
  `mod_active` int(1) unsigned NOT NULL DEFAULT '0',
  `mod_ui_name` varchar(20) NOT NULL DEFAULT '',
  `mod_ui_icon` varchar(64) NOT NULL DEFAULT '',
  `mod_ui_order` tinyint(3) NOT NULL DEFAULT '0',
  `mod_ui_active` int(1) unsigned NOT NULL DEFAULT '0',
  `mod_description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`mod_id`,`mod_directory`)
) /*! ENGINE=MyISAM */  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Contenu de la table `modules`
--

INSERT INTO `modules` (`mod_id`, `mod_name`, `mod_directory`, `mod_version`, `mod_setup_class`, `mod_type`, `mod_active`, `mod_ui_name`, `mod_ui_icon`, `mod_ui_order`, `mod_ui_active`, `mod_description`) VALUES
(1, 'system', 'system', '1.0.13', '', 'core', 1, 'System Admin', 'system.png', 1, 1, ''),
(2, 'admin', 'admin', '1.0.14', '', 'core', 1, 'User Admin', 'admin.png', 2, 1, ''),
(3, 'dPetablissement', 'dPetablissement', '0.19', '', 'core', 1, 'Groups admin', 'dPetablissement.png', 3, 1, ''),
(4, 'dPdeveloppement', 'dPdeveloppement', '0.0', '', 'core', 1, 'Groups admin', 'ddPdeveloppement.png', 4, 1, '');

-- --------------------------------------------------------

--
-- Structure de la table `note`
--

CREATE TABLE IF NOT EXISTS `note` (
  `note_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `object_id` int(11) unsigned NOT NULL,
  `object_class` varchar(25) NOT NULL,
  `public` enum('0','1') NOT NULL DEFAULT '0',
  `degre` enum('low','high') NOT NULL DEFAULT 'low',
  `date` datetime NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `text` text,
  PRIMARY KEY (`note_id`),
  KEY `user_id` (`user_id`,`object_id`,`object_class`,`public`,`degre`,`date`)
) /*! ENGINE=MyISAM */ DEFAULT CHARSET=latin1 COMMENT='Table des notes sur les objets' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_user` int(11) NOT NULL DEFAULT '0',
  `permission_grant_on` varchar(12) NOT NULL DEFAULT '',
  `permission_item` int(11) NOT NULL DEFAULT '0',
  `permission_value` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `idx_pgrant_on` (`permission_grant_on`,`permission_item`,`permission_user`),
  KEY `idx_puser` (`permission_user`),
  KEY `idx_pvalue` (`permission_value`)
) /*! ENGINE=MyISAM */  DEFAULT CHARSET=latin1 AUTO_INCREMENT=80 ;

--
-- Contenu de la table `permissions`
--

INSERT INTO `permissions` (`permission_id`, `permission_user`, `permission_grant_on`, `permission_item`, `permission_value`) VALUES
(1, 1, 'all', -1, -1),
(2, 2, 'mediusers', -1, 3),
(3, 2, 'dPccam', -1, -1),
(4, 2, 'dPcim10', -1, -1),
(5, 2, 'dPadmissions', -1, -1),
(6, 2, 'dPpatients', -1, -1),
(7, 2, 'dPhospi', -1, 1),
(8, 3, 'dPccam', -1, -1),
(9, 3, 'dPcim10', -1, -1),
(10, 3, 'dPpatients', -1, -1),
(11, 3, 'dPhospi', -1, 1),
(12, 3, 'dPbloc', -1, 1),
(13, 3, 'dPplanningOp', -1, -1),
(14, 3, 'dPcabinet', -1, -1),
(15, 3, 'dPcompteRend', -1, -1),
(16, 4, 'dPccam', -1, -1),
(17, 4, 'dPcim10', -1, -1),
(18, 4, 'dPpatients', -1, -1),
(19, 4, 'dPhospi', -1, 1),
(20, 4, 'dPbloc', -1, 1),
(21, 4, 'dPplanningOp', -1, -1),
(22, 4, 'dPcabinet', -1, -1),
(23, 4, 'dPcompteRend', -1, -1),
(24, 5, 'all', -1, -1),
(25, 5, 'dPcabinet', -1, 0),
(26, 5, 'dPgestionCab', -1, 0),
(27, 5, 'dPinterop', -1, 0),
(28, 6, 'mediusers', -1, 3),
(29, 6, 'dPccam', -1, -1),
(30, 6, 'dPcim10', -1, -1),
(31, 6, 'dPadmissions', -1, 1),
(32, 6, 'dPpatients', -1, -1),
(33, 6, 'dPhospi', -1, 1),
(34, 6, 'dPbloc', -1, 1),
(35, 6, 'dPpmsi', -1, 1),
(36, 7, 'mediusers', -1, 3),
(37, 7, 'dPccam', -1, -1),
(38, 7, 'dPcim10', -1, -1),
(39, 7, 'dPadmissions', -1, -1),
(40, 7, 'dPpatients', -1, -1),
(41, 7, 'dPhospi', -1, -1),
(42, 7, 'dPbloc', -1, 1),
(43, 8, 'mediusers', -1, 3),
(44, 8, 'dPccam', -1, -1),
(45, 8, 'dPcim10', -1, -1),
(46, 8, 'dPadmissions', -1, 1),
(47, 8, 'dPpatients', -1, -1),
(48, 8, 'dPhospi', -1, 1),
(49, 8, 'dPbloc', -1, 1),
(50, 8, 'dPpmsi', -1, -1),
(51, 9, 'mediusers', -1, 3),
(52, 9, 'dPccam', -1, -1),
(53, 9, 'dPcim10', -1, -1),
(54, 9, 'dPadmissions', -1, 1),
(55, 9, 'dPpatients', -1, -1),
(56, 9, 'dPhospi', -1, 1),
(57, 9, 'dPbloc', -1, 1),
(58, 9, 'dPstats', -1, -1),
(59, 10, 'dPccam', -1, -1),
(60, 10, 'dPcim10', -1, -1),
(61, 10, 'dPpatients', -1, -1),
(62, 10, 'dPhospi', -1, 1),
(63, 10, 'dPbloc', -1, 1),
(64, 10, 'dPplanningOp', -1, -1),
(65, 10, 'dPcabinet', -1, -1),
(66, 10, 'dPcompteRend', -1, -1),
(67, 11, 'all', -1, -1),
(68, 12, 'mediusers', -1, 3),
(69, 12, 'dPccam', -1, -1),
(70, 12, 'dPcim10', -1, -1),
(71, 12, 'dPadmissions', -1, 1),
(72, 12, 'dPpatients', -1, -1),
(73, 12, 'dPhospi', -1, 1),
(74, 12, 'dPbloc', -1, -1),
(75, 12, 'dPsalleOp', -1, -1),
(76, 13, 'mediusers', -1, 3),
(77, 13, 'dPccam', -1, -1),
(78, 13, 'dPcim10', -1, -1),
(79, 13, 'dPsalleOp', -1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `perm_module`
--

CREATE TABLE IF NOT EXISTS `perm_module` (
  `perm_module_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `mod_id` int(11) unsigned DEFAULT NULL,
  `permission` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0',
  `view` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0',
  PRIMARY KEY (`perm_module_id`),
  UNIQUE KEY `user_id` (`user_id`,`mod_id`)
) /*! ENGINE=MyISAM */  DEFAULT CHARSET=latin1 COMMENT='table des permissions sur les modules' AUTO_INCREMENT=4 ;

--
-- Contenu de la table `perm_module`
--

INSERT INTO `perm_module` (`perm_module_id`, `user_id`, `mod_id`, `permission`, `view`) VALUES
(1, 1, NULL, 2, 2),
(2, 5, NULL, 2, 1),
(3, 11, NULL, 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `perm_object`
--

CREATE TABLE IF NOT EXISTS `perm_object` (
  `perm_object_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `object_id` int(11) unsigned DEFAULT NULL,
  `object_class` varchar(255) NOT NULL,
  `permission` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0',
  PRIMARY KEY (`perm_object_id`),
  UNIQUE KEY `user_id` (`user_id`,`object_id`,`object_class`)
) /*! ENGINE=MyISAM */ DEFAULT CHARSET=latin1 COMMENT='Table des permissions sur les objets' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_username` varchar(20) NOT NULL DEFAULT '',
  `user_password` varchar(255) NOT NULL,
  `user_password_last_change` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_salt` CHAR(64) NULL DEFAULT NULL,
  `user_type` tinyint(4) NOT NULL DEFAULT '0',
  `user_first_name` varchar(50) DEFAULT '',
  `user_last_name` varchar(50) NOT NULL,
  `user_email` varchar(255) DEFAULT '',
  `user_phone` varchar(30) DEFAULT '',
  `user_mobile` varchar(30) DEFAULT '',
  `user_address1` varchar(50) DEFAULT NULL,
  `user_city` varchar(30) DEFAULT '',
  `user_zip` varchar(11) DEFAULT '',
  `user_country` varchar(30) DEFAULT '',
  `user_birthday` datetime DEFAULT NULL,
  `user_pic` text,
  `user_signature` text,
  `user_last_login` datetime DEFAULT NULL,
  `user_login_errors` tinyint(4) DEFAULT '0',
  `template` enum('0','1') NOT NULL DEFAULT '0',
  `profile_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `idx_uid` (`user_username`),
  KEY `idx_pwd` (`user_password`)
) /*! ENGINE=MyISAM */  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

--
-- Contenu de la table `users`
--

INSERT INTO `users` (`user_id`, `user_username`, `user_password`, `user_type`, `user_first_name`, `user_last_name`, `user_email`, `user_phone`, `user_mobile`, `user_address1`, `user_city`, `user_zip`, `user_country`, `user_birthday`, `user_pic`, `user_signature`, `user_last_login`, `user_login_errors`, `template`, `profile_id`) VALUES
( 1, 'admin'          , '5678708573db51f90da2095407abc45e',  1, 'Admin'          , 'Person'         , '', '', '', '', '', '', '', NULL, NULL, '', NULL, 0, '0', NULL),
( 2, 'Accueil'        , '5678708573db51f90da2095407abc45e',  2, 'accueil'        , 'accueil'        , '', '', '', '', '', '', '', NULL, NULL, '', NULL, 0, '1', NULL),
( 3, 'Anesthesie'     , '5678708573db51f90da2095407abc45e',  4, 'anesthesie'     , 'anesthesie'     , '', '', '', '', '', '', '', NULL, NULL, '', NULL, 0, '1', NULL),
( 4, 'Chirurgie'      , '5678708573db51f90da2095407abc45e',  3, 'chirurgie'      , 'chirurgie'      , '', '', '', '', '', '', '', NULL, NULL, '', NULL, 0, '1', NULL),
( 5, 'Direction'      , '5678708573db51f90da2095407abc45e',  5, 'direction'      , 'direction'      , '', '', '', '', '', '', '', NULL, NULL, '', NULL, 0, '1', NULL),
( 6, 'Facturation'    , '5678708573db51f90da2095407abc45e',  6, 'facturation'    , 'facturation'    , '', '', '', '', '', '', '', NULL, NULL, '', NULL, 0, '1', NULL),
( 7, 'Hospitalisation', '5678708573db51f90da2095407abc45e',  7, 'Hospitalisation', 'Hospitalisation', '', '', '', '', '', '', '', NULL, NULL, '', NULL, 0, '1', NULL),
( 8, 'PMSI'           , '5678708573db51f90da2095407abc45e',  8, 'PMSI'           , 'PMSI'           , '', '', '', '', '', '', '', NULL, NULL, '', NULL, 0, '1', NULL),
( 9, 'Qualite'        , '5678708573db51f90da2095407abc45e',  9, 'qualite'        , 'qualite'        , '', '', '', '', '', '', '', NULL, NULL, '', NULL, 0, '1', NULL),
(10, 'Secretariat'    , '5678708573db51f90da2095407abc45e', 10, 'secretariat'    , 'secretariat'    , '', '', '', '', '', '', '', NULL, NULL, '', NULL, 0, '1', NULL),
(11, 'SI'             , '5678708573db51f90da2095407abc45e',  1, 'SI'             , 'SI'             , '', '', '', '', '', '', '', NULL, NULL, '', NULL, 0, '1', NULL),
(12, 'Survbloc'       , '5678708573db51f90da2095407abc45e', 12, 'survbloc'       , 'survbloc'       , '', '', '', '', '', '', '', NULL, NULL, '', NULL, 0, '1', NULL),
(13, 'Bloc'           , '5678708573db51f90da2095407abc45e', 12, 'bloc'           , 'bloc'           , '', '', '', '', '', '', '', NULL, NULL, '', NULL, 0, '1', NULL);
-- --------------------------------------------------------

--
-- Structure de la table `user_log`
--

CREATE TABLE IF NOT EXISTS `user_log` (
  `user_log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `object_id` int(11) unsigned NOT NULL DEFAULT '0',
  `object_class` varchar(255) NOT NULL,
  `type` enum('create','store','merge','delete') NOT NULL,
  `date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `fields` text,
  PRIMARY KEY (`user_log_id`),
  KEY `user_id` (`user_id`),
  KEY `object_id` (`object_id`),
  KEY `object_class` (`object_class`),
  KEY `date` (`date`)
) /*! ENGINE=MyISAM */ DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `user_preferences`
--

CREATE TABLE IF NOT EXISTS `user_preferences` (
  `pref_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
  `user_id` INT (11) UNSIGNED NULL DEFAULT NULL,
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL DEFAULT ''
) /*! ENGINE=MyISAM */ DEFAULT CHARSET=latin1;

--
-- Contenu de la table `user_preferences`
--

INSERT INTO `user_preferences` (`user_id`, `key`, `value`) VALUES
('0', 'LOCALE', 'fr'),
('0', 'UISTYLE', 'e-cap'),
('0', 'MenuPosition', 'left'),
('0', 'DEFMODULE', 'system'),
('0', 'INFOSYSTEM', '0'),

('1', 'LOCALE', 'fr'),
('1', 'UISTYLE', 'e-cap'),
('1', 'MenuPosition', 'left'),
('1', 'DEFMODULE', 'system'),
('1', 'INFOSYSTEM', '1');

-- --------------------------------------------------------

--
-- Structure de la table `user_action`
--

CREATE TABLE IF NOT EXISTS `user_action` (
  `user_action_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `object_id` INT(11) UNSIGNED NOT NULL,
  `object_class_id` INT(11) UNSIGNED NOT NULL,
  `type` ENUM('create','store','merge','delete') NOT NULL,
  `date` DATETIME NOT NULL,
  `ip_address` VARBINARY(16) NULL DEFAULT NULL,
  PRIMARY KEY (`user_action_id`),
  INDEX `user_id` (`user_id`),
  INDEX `object_ref` (`object_class_id`, `object_id`),
  INDEX `date` (`date`)
) /*! ENGINE=MyISAM AUTO_INCREMENT=1000000000 */ DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `user_action_data`
--

CREATE TABLE IF NOT EXISTS `user_action_data` (
  `user_action_data_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_action_id` INT(11) UNSIGNED NOT NULL,
  `field` VARCHAR(250) NULL DEFAULT NULL,
  `value` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`user_action_data_id`),
  INDEX `user_action_id` (`user_action_id`)
) /*! ENGINE=MyISAM */ DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `object_class`
--

CREATE TABLE IF NOT EXISTS `object_class` (
  `object_class_id` INT(11) NOT NULL AUTO_INCREMENT,
  `object_class` VARCHAR(250) NOT NULL,
  PRIMARY KEY (`object_class_id`),
  UNIQUE INDEX `object_class` (`object_class`)
) /*! ENGINE=MyISAM */ DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table table_status
--
CREATE TABLE `table_status` (
  `table_status_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
  `name` VARCHAR (255) NOT NULL,
  `create_time` DATETIME NOT NULL,
  `update_time` DATETIME NOT NULL,
  INDEX `name` (`name`)
)/*! ENGINE=MyISAM */ DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table configuration
--
CREATE TABLE `configuration` (
  `configuration_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
  `feature` VARCHAR (255) NOT NULL,
  `value` VARCHAR (1024),
  `alt_value` VARCHAR(1024),
  `object_id` INT (11) UNSIGNED,
  `object_class` VARCHAR (80),
  INDEX (`object_id`, `object_class`),
  UNIQUE INDEX (`feature`, `object_id`, `object_class`)
) /*! ENGINE=MyISAM */  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table user_authentication_error
--
CREATE TABLE `user_authentication_error` (
  `user_authentication_error_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
  `user_id` INT (11) UNSIGNED,
  `login_value` VARCHAR (80) NOT NULL,
  `datetime` DATETIME NOT NULL,
  `auth_method` VARCHAR (30),
  `identifier` VARCHAR (16) NOT NULL,
  `ip_address` VARCHAR (39) NOT NULL,
  `message` TEXT,
  INDEX (`user_id`),
  INDEX (`datetime`),
  INDEX (`identifier`)
)/*! ENGINE=MyISAM */ DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

INSERT INTO `users` (`user_id`, `user_username`, `user_password`, `user_password_last_change`, `user_salt`, `user_type`, `user_first_name`, `user_last_name`, `user_email`, `user_phone`, `user_mobile`, `user_address1`, `user_city`, `user_zip`, `user_country`, `user_birthday`, `user_pic`, `user_signature`, `user_last_login`, `user_login_errors`, `template`, `profile_id`) VALUES (" . self::USER_PHPUNIT_ID . ", 'PHPUnit', '8afa1545a5103789c5dc737de3b2cd72394e9739863c5bd7175fea8542d71ab8', '2022-04-07 15:04:27', '9bcf8cfe97aec9ebec445c20675ffbe5a5755b7204623e2dad94880a1b6b8494', 1, 'Phpunit', 'XUNIT', '', '', '', '', '', '', '', NULL, NULL, '', NULL, 0, '0', NULL);
