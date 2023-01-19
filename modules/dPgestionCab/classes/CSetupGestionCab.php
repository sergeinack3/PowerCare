<?php
/**
 * @package Mediboard\GestionCab
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\GestionCab;

use Ox\Core\CSetup;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * @codeCoverageIgnore
 */
class CSetupGestionCab extends CSetup {
  /**
   * Crée les employés du cabinet
   *
   * @return bool
   */
  protected function createEmployes() {
    $param = new CParamsPaie();
    $params = $param->loadList();
    if (!is_array($params)) {
      return true;
    }
    foreach ($params as $key => $curr_param) {
      $user = new CMediusers();
      $user->load($params[$key]->employecab_id);
      $employe = new CEmployeCab();
      $employe->function_id = $user->function_id;
      $employe->nom         = $user->_user_last_name;
      $employe->prenom      = $user->_user_first_name;
      $employe->function    = $user->_user_type;
      $employe->adresse     = $user->_user_adresse;
      $employe->cp          = $user->_user_cp;
      $employe->ville       = $user->_user_ville;
      $employe->store();
      $params[$key]->employecab_id = $employe->employecab_id;
      $params[$key]->store();
    }
    return true;
  }

  /**
   * @see parent::__construct()
   */
  function __construct() {
    parent::__construct();

    $this->mod_name = "dPgestionCab";

    $this->makeRevision("0.0");
    $query = "CREATE TABLE `gestioncab` (
                  `gestioncab_id` INT NOT NULL AUTO_INCREMENT ,
                  `function_id` INT NOT NULL ,
                  `libelle` VARCHAR( 50 ) DEFAULT 'inconnu' NOT NULL ,
                  `date` DATE NOT NULL ,
                  `rubrique_id` INT DEFAULT '0' NOT NULL ,
                  `montant` FLOAT DEFAULT '0' NOT NULL ,
                  `mode_paiement_id` INT DEFAULT '0' NOT NULL ,
                  `num_facture` INT NOT NULL ,
                  `rques` TEXT,
                  PRIMARY KEY ( `gestioncab_id` ) ,
                  INDEX ( `function_id` , `rubrique_id` , `mode_paiement_id` )
                ) /*! ENGINE=MyISAM */ COMMENT = 'Table des lignes de la comptabilité de cabinet';";
    $this->addQuery($query);
    $query = "CREATE TABLE `rubrique_gestioncab` (
                  `rubrique_id` INT NOT NULL AUTO_INCREMENT ,
                  `function_id` INT DEFAULT '0' NOT NULL ,
                  `nom` VARCHAR( 30 ) DEFAULT 'divers' NOT NULL ,
                  PRIMARY KEY ( `rubrique_id` ) ,
                  INDEX ( `function_id` )
                ) /*! ENGINE=MyISAM */ COMMENT = 'Table des rubriques pour la gestion comptable de cabinet';";
    $this->addQuery($query);
    $query = "INSERT INTO `rubrique_gestioncab` ( `rubrique_id` , `function_id` , `nom` )
                VALUES ('', '0', 'divers');";
    $this->addQuery($query);
    $query = "CREATE TABLE `mode_paiement` (
                  `mode_paiement_id` INT NOT NULL AUTO_INCREMENT ,
                  `function_id` INT DEFAULT '0' NOT NULL ,
                  `nom` VARCHAR( 30 ) DEFAULT 'inconnu' NOT NULL ,
                  PRIMARY KEY ( `mode_paiement_id` ) ,
                  INDEX ( `function_id` )
                ) /*! ENGINE=MyISAM */ COMMENT = 'Table des modes de règlement';";
    $this->addQuery($query);
    $query = "INSERT INTO `mode_paiement` ( `mode_paiement_id` , `function_id` , `nom` ) VALUES ('', '0', 'Chèque');";
    $this->addQuery($query);
    $query = "INSERT INTO `mode_paiement` ( `mode_paiement_id` , `function_id` , `nom` ) VALUES ('', '0', 'CB');";
    $this->addQuery($query);
    $query = "INSERT INTO `mode_paiement` ( `mode_paiement_id` , `function_id` , `nom` ) VALUES ('', '0', 'Virement');";
    $this->addQuery($query);
    $query = "INSERT INTO `mode_paiement` ( `mode_paiement_id` , `function_id` , `nom` ) VALUES ('', '0', 'Prélèvement');";
    $this->addQuery($query);
    $query = "INSERT INTO `mode_paiement` ( `mode_paiement_id` , `function_id` , `nom` ) VALUES ('', '0', 'TIP');";
    $this->addQuery($query);
    $query = "CREATE TABLE `params_paie` (
                  `params_paie_id` BIGINT NOT NULL AUTO_INCREMENT ,
                  `user_id` BIGINT NOT NULL ,
                  `smic` FLOAT NOT NULL ,
                  `csgds` FLOAT NOT NULL ,
                  `csgnds` FLOAT NOT NULL ,
                  `ssms` FLOAT NOT NULL ,
                  `ssmp` FLOAT NOT NULL ,
                  `ssvs` FLOAT NOT NULL ,
                  `ssvp` FLOAT NOT NULL ,
                  `rcs` FLOAT NOT NULL ,
                  `rcp` FLOAT NOT NULL ,
                  `agffs` FLOAT NOT NULL ,
                  `agffp` FLOAT NOT NULL ,
                  `aps` FLOAT NOT NULL ,
                  `app` FLOAT NOT NULL ,
                  `acs` FLOAT NOT NULL ,
                  `acp` FLOAT NOT NULL ,
                  `aatp` FLOAT NOT NULL ,
                  `nom` VARCHAR(100) NOT NULL ,
                  `adresse` VARCHAR(50) NOT NULL ,
                  `cp` VARCHAR(5) NOT NULL ,
                  `ville` VARCHAR(50) NOT NULL ,
                  `siret` VARCHAR(14) NOT NULL ,
                  `ape` VARCHAR(4) NOT NULL ,
                  PRIMARY KEY ( `params_paie_id` ) ,
                  INDEX ( `user_id` )
                ) /*! ENGINE=MyISAM */ COMMENT = 'Paramètres fiscaux pour les fiches de paie';";
    $this->addQuery($query);
    $query = "CREATE TABLE `fiche_paie` (
                  `fiche_paie_id` BIGINT NOT NULL AUTO_INCREMENT ,
                  `params_paie_id` BIGINT NOT NULL ,
                  `debut` DATE NOT NULL ,
                  `fin` DATE NOT NULL ,
                  `salaire` FLOAT NOT NULL ,
                  `heures` SMALLINT NOT NULL ,
                  `heures_sup` SMALLINT NOT NULL ,
                  `mutuelle` FLOAT NOT NULL ,
                  `precarite` FLOAT NOT NULL ,
                  `anciennete` FLOAT NOT NULL ,
                  PRIMARY KEY ( `fiche_paie_id` ) ,
                  INDEX ( `params_paie_id` )
                ) /*! ENGINE=MyISAM */ COMMENT = 'Table des fiches de paie';";
    $this->addQuery($query);

    $this->makeRevision("0.1");
    $query = "ALTER TABLE fiche_paie ADD `conges_payes` FLOAT NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.11");
    $query = "ALTER TABLE fiche_paie ADD `prime_speciale` FLOAT NOT NULL";
    $this->addQuery($query);
    $query = "ALTER TABLE params_paie ADD `matricule` VARCHAR(15)";
    $this->addQuery($query);

    $this->makeRevision("0.12");
    $this->addDependency("mediusers", "0.1");
    $query = "CREATE TABLE `employecab` (
                `employecab_id` INT NOT NULL AUTO_INCREMENT,
                `function_id` INT NOT NULL DEFAULT '0',
                `nom` VARCHAR( 50 ) NOT NULL DEFAULT '',
                `prenom` VARCHAR( 50 ) NOT NULL DEFAULT '',
                `function` VARCHAR( 50 ) NOT NULL DEFAULT '',
                `adresse` VARCHAR( 50 ),
                `cp` VARCHAR( 5 ),
                `ville` VARCHAR( 50 ),
                PRIMARY KEY ( `employecab_id` ),
                INDEX ( `function_id` )
              ) /*! ENGINE=MyISAM */ COMMENT = 'Table des employes';";
    $this->addQuery($query);
    $query = "ALTER TABLE `params_paie` CHANGE `user_id` `employecab_id` INT NOT NULL DEFAULT '0';";
    $this->addQuery($query);

    $this->addMethod("createEmployes");

    $this->makeRevision("0.13");
    $query = "ALTER TABLE `employecab`
                CHANGE `employecab_id` `employecab_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `function_id` `function_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `nom` `nom` varchar(255) NOT NULL,
                CHANGE `prenom` `prenom` varchar(255) NOT NULL,
                CHANGE `function` `function` varchar(255) NOT NULL,
                CHANGE `adresse` `adresse` varchar(255) NULL,
                CHANGE `cp` `cp` int(5) unsigned zerofill NULL,
                CHANGE `ville` `ville` varchar(255) NULL;";
    $this->addQuery($query);
    $query = "ALTER TABLE `fiche_paie`
                CHANGE `fiche_paie_id` `fiche_paie_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `params_paie_id` `params_paie_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `heures` `heures` tinyint(4) NOT NULL DEFAULT '0',
                CHANGE `heures_sup` `heures_sup` tinyint(4) NOT NULL DEFAULT '0';";
    $this->addQuery($query);
    $query = "ALTER TABLE `gestioncab`
                CHANGE `gestioncab_id` `gestioncab_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `function_id` `function_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `libelle` `libelle` varchar(255) NOT NULL DEFAULT 'inconnu',
                CHANGE `rubrique_id` `rubrique_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `mode_paiement_id` `mode_paiement_id` int(11) unsigned NOT NULL DEFAULT '0';";
    $this->addQuery($query);
    $query = "ALTER TABLE `mode_paiement`
                CHANGE `mode_paiement_id` `mode_paiement_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `function_id` `function_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `nom` `nom` varchar(255) NOT NULL DEFAULT 'inconnu';";
    $this->addQuery($query);
    $query = "ALTER TABLE `params_paie`
                CHANGE `params_paie_id` `params_paie_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `employecab_id` `employecab_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `nom` `nom` varchar(255) NOT NULL,
                CHANGE `adresse` `adresse` varchar(255) NOT NULL,
                CHANGE `cp` `cp` int(5) unsigned zerofill NOT NULL,
                CHANGE `ville` `ville` varchar(255) NOT NULL,
                CHANGE `siret` `siret` bigint(14) unsigned zerofill NOT NULL;";
    $this->addQuery($query);
    $query = "ALTER TABLE `rubrique_gestioncab`
                CHANGE `rubrique_id` `rubrique_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `function_id` `function_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `nom` `nom` varchar(255) NOT NULL DEFAULT 'divers';";
    $this->addQuery($query);

    $this->makeRevision("0.14");
    $query = "ALTER TABLE `mode_paiement` CHANGE `function_id` `function_id` int(11) unsigned NULL DEFAULT NULL;";
    $this->addQuery($query);
    $query = "UPDATE `mode_paiement` SET `function_id` = NULL WHERE `function_id` = '0';";
    $this->addQuery($query);
    $query = "ALTER TABLE `rubrique_gestioncab` CHANGE `function_id` `function_id` int(11) unsigned NULL DEFAULT NULL;";
    $this->addQuery($query);
    $query = "UPDATE `rubrique_gestioncab` SET `function_id` = NULL WHERE `function_id` = '0';";
    $this->addQuery($query);

    $this->makeRevision("0.15");
    $query = "ALTER TABLE `params_paie` ADD `csp` FLOAT NOT NULL DEFAULT 0";
    $this->addQuery($query);

    $this->makeRevision("0.16");
    $query = "ALTER TABLE `params_paie`
            CHANGE `ape` `ape` VARCHAR(6);";
    $this->addQuery($query);

    $this->makeRevision("0.17");
    $query = "ALTER TABLE `fiche_paie`
            DROP `mutuelle`,
            ADD `final_file` MEDIUMTEXT;";
    $this->addQuery($query);
    $query = "ALTER TABLE `params_paie`
            ADD `ms` FLOAT NOT NULL AFTER `csp`,
            ADD `mp` FLOAT NOT NULL AFTER `ms`;";
    $this->addQuery($query);

    $this->makeRevision("0.18");
    $query = "ALTER TABLE `fiche_paie`
            ADD `heures_comp` tinyint(4) NOT NULL DEFAULT '0' AFTER `heures`;";
    $this->addQuery($query);
    $query = "ALTER TABLE `params_paie`
            ADD `csgnis` FLOAT NOT NULL AFTER `smic`;";
    $this->addQuery($query);

    $this->makeRevision("0.19");
    $query = "ALTER TABLE `fiche_paie` 
              ADD INDEX (`debut`),
              ADD INDEX (`fin`);";
    $this->addQuery($query);
    $query = "ALTER TABLE `gestioncab` 
              ADD INDEX (`date`);";
    $this->addQuery($query);

    $this->makeRevision('0.20');

    $query = "ALTER TABLE `params_paie` CHANGE `cp` `cp` VARCHAR (5);";
    $this->addQuery($query);

    $query = "ALTER TABLE `employecab` CHANGE `cp` `cp` VARCHAR (5);";
    $this->addQuery($query);

    $this->makeRevision("0.21");
    $this->setModuleCategory("administratif", "metier");

    $this->mod_version = '0.22';
  }
}
