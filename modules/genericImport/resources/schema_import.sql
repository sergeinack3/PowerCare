-- Listage de la structure de la table import. actes_ccam
CREATE TABLE IF NOT EXISTS `acte_ccam` (
  `id` varchar(80) NOT NULL,
  `consultation` varchar(80) DEFAULT NULL,
  `executant` varchar(80) DEFAULT NULL,
  `code_acte` varchar(255) DEFAULT NULL,
  `date_execution` datetime DEFAULT NULL,
  `code_activite` varchar(255) DEFAULT NULL,
  `code_phase` varchar(255) DEFAULT NULL,
  `modificateurs` varchar(255) DEFAULT NULL,
  `montant_base` varchar(255) DEFAULT NULL,
  `montant_depassement` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `executant` (`executant`),
  KEY `consultation` (`consultation`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table import. actes_ngap
CREATE TABLE IF NOT EXISTS `acte_ngap` (
  `id` varchar(80) NOT NULL,
  `consultation` varchar(80) DEFAULT NULL,
  `executant` varchar(80) DEFAULT NULL,
  `code_acte` varchar(255) DEFAULT NULL,
  `date_execution` datetime DEFAULT NULL,
  `quantite` varchar(255) DEFAULT NULL,
  `coefficient` varchar(255) DEFAULT NULL,
  `montant_base` varchar(255) DEFAULT NULL,
  `montant_depassement` varchar(255) DEFAULT NULL,
  `numero_dent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `executant` (`executant`),
  KEY `consultation` (`consultation`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table import. affectation
CREATE TABLE IF NOT EXISTS `affectation` (
  `id` varchar(80) NOT NULL,
  `sejour` varchar(80) DEFAULT NULL,
  `nom_service` varchar(80) DEFAULT NULL,
  `nom_lit` varchar(80) DEFAULT NULL,
  `entree` datetime DEFAULT NULL,
  `sortie` datetime DEFAULT NULL,
  `remarques` text DEFAULT NULL,
  `effectue` varchar(11) DEFAULT NULL,
  `mode_entree` varchar(255) DEFAULT NULL,
  `mode_sortie` varchar(255) DEFAULT NULL,
  `code_unite_fonctionnelle` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sejour` (`sejour`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table import. antecedent
CREATE TABLE IF NOT EXISTS `antecedent` (
  `id` varchar(80) NOT NULL,
  `praticien` varchar(80) DEFAULT NULL,
  `patient` varchar(80) DEFAULT NULL,
  `text` text DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `date` date DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `praticien` (`praticien`),
  KEY `patient` (`patient`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table import. constante
CREATE TABLE IF NOT EXISTS `constante` (
  `id` varchar(80) NOT NULL,
  `praticien` varchar(80) DEFAULT NULL,
  `patient` varchar(80) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `taille` varchar(255) DEFAULT NULL,
  `poids` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `praticien` (`praticien`),
  KEY `patient` (`patient`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table import. consultation
CREATE TABLE IF NOT EXISTS `consultation` (
  `id` varchar(80) NOT NULL,
  `date` date DEFAULT NULL,
  `praticien` varchar(80) DEFAULT NULL,
  `heure` time DEFAULT NULL,
  `duree` time DEFAULT NULL,
  `motif` varchar(255) DEFAULT NULL,
  `remarques` text DEFAULT NULL,
  `examen` text DEFAULT NULL,
  `traitement` text DEFAULT NULL,
  `histoire_maladie` text DEFAULT NULL,
  `conclusion` text DEFAULT NULL,
  `resultats` text DEFAULT NULL,
  `patient` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `praticien` (`praticien`),
  KEY `patient` (`patient`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table import. correspondant_medical
CREATE TABLE IF NOT EXISTS `correspondant_medical` (
  `id` varchar(80) NOT NULL,
  `medecin` varchar(80) DEFAULT NULL,
  `patient` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medecin` (`medecin`),
  KEY `patient` (`patient`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table import. dossier_medical
CREATE TABLE IF NOT EXISTS `dossier_medical` (
  `id` varchar(80) NOT NULL,
  `patient` varchar(80) DEFAULT NULL,
  `groupe_sanguin` varchar(10) DEFAULT NULL,
  `rhesus` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient` (`patient`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table import. evenement_patient
CREATE TABLE IF NOT EXISTS `evenement_patient` (
  `id` varchar(80) NOT NULL,
  `praticien` varchar(80) DEFAULT NULL,
  `patient` varchar(80) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `libelle` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `praticien` (`praticien`),
  KEY `patient` (`patient`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table import. fichier
CREATE TABLE IF NOT EXISTS `fichier` (
  `id` varchar(80) NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `auteur` varchar(80) DEFAULT NULL,
  `consultation` varchar(80) DEFAULT NULL,
  `sejour` varchar(80) DEFAULT NULL,
  `patient` varchar(80) DEFAULT NULL,
  `evenement` varchar(80) DEFAULT NULL,
  `categorie` varchar(255) DEFAULT NULL,
  `contenu` blob DEFAULT NULL,
  `chemin` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `auteur` (`auteur`),
  KEY `evenement` (`evenement`),
  KEY `patient` (`patient`),
  KEY `sejour` (`sejour`),
  KEY `consultation` (`consultation`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table import. intervention
CREATE TABLE IF NOT EXISTS `intervention` (
  `id` varchar(80) NOT NULL,
  `sejour` varchar(80) DEFAULT NULL,
  `chir` varchar(80) DEFAULT NULL,
  `cote` varchar(255) DEFAULT NULL,
  `date_intervention` datetime DEFAULT NULL,
  `patient` varchar(80) DEFAULT NULL,
  `libelle` varchar(255) DEFAULT NULL,
  `examen` text DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sejour` (`sejour`),
  KEY `chir` (`chir`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table import. medecin
CREATE TABLE IF NOT EXISTS `medecin` (
  `id` varchar(80) NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `sexe` varchar(255) DEFAULT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `disciplines` varchar(255) DEFAULT NULL,
  `tel` varchar(255) DEFAULT NULL,
  `tel_autre` varchar(255) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `cp` varchar(255) DEFAULT NULL,
  `ville` varchar(255) DEFAULT NULL,
  `rpps` varchar(255) DEFAULT NULL,
  `adeli` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table import. patient
CREATE TABLE IF NOT EXISTS `patient` (
  `id` varchar(80) NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `nom_naissance` varchar(255) DEFAULT NULL,
  `profession` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `tel` varchar(255) DEFAULT NULL,
  `tel2` varchar(255) DEFAULT NULL,
  `tel_autre` varchar(255) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `cp` varchar(255) DEFAULT NULL,
  `ville` varchar(255) DEFAULT NULL,
  `pays` varchar(255) DEFAULT NULL,
  `matricule` varchar(255) DEFAULT NULL,
  `sexe` varchar(255) DEFAULT NULL,
  `civilite` varchar(255) DEFAULT NULL,
  `remarques` varchar(255) DEFAULT NULL,
  `medecin_traitant` varchar(255) DEFAULT NULL,
  `ald` varchar(255) DEFAULT NULL,
  `ipp` varchar(255) DEFAULT NULL,
  `nom_assure` varchar(255) DEFAULT NULL,
  `prenom_assure` varchar(255) DEFAULT NULL,
  `nom_naissance_assure` varchar(255) DEFAULT NULL,
  `sexe_assure` varchar(255) DEFAULT NULL,
  `civilite_assure` varchar(255) DEFAULT NULL,
  `naissance_assure` varchar(255) DEFAULT NULL,
  `adresse_assure` varchar(255) DEFAULT NULL,
  `ville_assure` varchar(255) DEFAULT NULL,
  `cp_assure` varchar(255) DEFAULT NULL,
  `pays_assure` varchar(255) DEFAULT NULL,
  `tel_assure` varchar(255) DEFAULT NULL,
  `matricule_assure` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table import. sejour
CREATE TABLE IF NOT EXISTS `sejour` (
  `id` varchar(80) NOT NULL,
  `entree_prevue` datetime DEFAULT NULL,
  `entree_reelle` datetime DEFAULT NULL,
  `sortie_prevue` datetime DEFAULT NULL,
  `sortie_reelle` datetime DEFAULT NULL,
  `libelle` varchar(255) DEFAULT NULL,
  `patient` varchar(80) DEFAULT NULL,
  `praticien` varchar(80) DEFAULT NULL,
  `prestation` varchar(255) DEFAULT NULL,
  `nda` varchar(255) DEFAULT NULL,
  `mode_traitement` varchar(255) DEFAULT NULL,
  `mode_entree` varchar(255) DEFAULT NULL,
  `mode_sortie` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `patient` (`patient`),
  KEY `praticien` (`praticien`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table import. traitement
CREATE TABLE IF NOT EXISTS `traitement` (
  `id` varchar(80) NOT NULL,
  `praticien` varchar(80) DEFAULT NULL,
  `patient` varchar(80) DEFAULT NULL,
  `text` text DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `praticien` (`praticien`),
  KEY `patient` (`patient`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Les données exportées n'étaient pas sélectionnées.

-- Listage de la structure de la table import. utilisateur
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id` varchar(80) NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
