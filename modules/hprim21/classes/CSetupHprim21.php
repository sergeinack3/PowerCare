<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21;

use Ox\Core\CRequest;
use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupHprim21 extends CSetup {
  /**
   * Add group_id Hprim21
   *
   * @return bool
   */
  protected function addingGroupSourceOscour() {
    $ds = $this->ds;

    $request = new CRequest();
    $request->addSelect("*");
    $request->addTable("groups_mediboard");

    $query  = $request->makeSelect();
    $groups = $ds->loadList($query);

    foreach ($groups as $_group) {
      $group_id = $_group["group_id"];
      $query    = "INSERT INTO `source_ftp`
                    SELECT  null,
                            `port` ,
                            `timeout` ,
                            `pasv` ,
                            `mode` ,
                            `fileprefix` ,
                            `fileextension` ,
                            `filenbroll` ,
                            `fileextension_write_end` ,
                            `counter` ,
                            CONCAT_WS('-', `name`, $group_id),
                            `host` ,
                            `user` ,
                            `password` ,
                            `iv` ,
                            `role` ,
                            `type_echange` ,
                            `active` ,
                            `loggable` ,
                            `ssl` ,
                            `libelle`
                    FROM  `source_ftp`
                    WHERE `name` = 'hprim21';";

      $ds->exec($query);
    }

    $query = "DELETE FROM `source_ftp` WHERE `name` = 'hprim21';";
    $ds->exec($query);

    return true;
  }

  function __construct() {
    parent::__construct();

    $this->mod_name = "hprim21";

    $this->makeRevision("0.0");
    $query = "CREATE TABLE `hprim21_patient` (
              `hprim21_patient_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              PRIMARY KEY (`hprim21_patient_id`)) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "ALTER TABLE `hprim21_patient`
              ADD `patient_id` INT(11) UNSIGNED, 
              ADD `nom` VARCHAR(255) NOT NULL, 
              ADD `prenom` VARCHAR(255), 
              ADD `prenom2` VARCHAR(255), 
              ADD `alias` VARCHAR(255), 
              ADD `civilite` ENUM('M','Mme','Mlle'), 
              ADD `diplome` VARCHAR(255), 
              ADD `nom_jeune_fille` VARCHAR(255), 
              ADD `naissance` DATE, 
              ADD `sexe` ENUM('M','F','U'), 
              ADD `adresse1` VARCHAR(255), 
              ADD `adresse2` VARCHAR(255), 
              ADD `ville` VARCHAR(255), 
              ADD `departement` VARCHAR(255), 
              ADD `cp` VARCHAR(255), 
              ADD `pays` VARCHAR(255), 
              ADD `telephone1` VARCHAR(255), 
              ADD `telephone2` VARCHAR(255), 
              ADD `traitement_local1` VARCHAR(255), 
              ADD `traitement_local2` VARCHAR(255), 
              ADD `taille` INT(11), 
              ADD `poids` INT(11), 
              ADD `diagnostic` VARCHAR(255), 
              ADD `traitement` VARCHAR(255), 
              ADD `regime` VARCHAR(255), 
              ADD `commentaire1` VARCHAR(255), 
              ADD `commentaire2` VARCHAR(255), 
              ADD `classification_diagnostic` VARCHAR(255), 
              ADD `situation_maritale` ENUM('M','S','D','W','A','U'), 
              ADD `precautions` VARCHAR(255), 
              ADD `langue` VARCHAR(255), 
              ADD `statut_confidentialite` VARCHAR(255), 
              ADD `date_derniere_modif` DATETIME, 
              ADD `date_deces` DATE, 
              ADD `nature_assurance` VARCHAR(255), 
              ADD `debut_validite` DATE, 
              ADD `fin_validite` DATE, 
              ADD `matricule` VARCHAR(15), 
              ADD `rang_beneficiaire` ENUM('01','02','09','11','12','13','14','15','16','31'), 
              ADD `rang_naissance` ENUM('1','2','3','4','5','6'), 
              ADD `code_regime` TINYINT(3) UNSIGNED ZEROFILL, 
              ADD `caisse_gest` MEDIUMINT(3) UNSIGNED ZEROFILL, 
              ADD `centre_gest` MEDIUMINT(4) UNSIGNED ZEROFILL, 
              ADD `origine_droits` VARCHAR(255), 
              ADD `nature_exoneration` VARCHAR(255), 
              ADD `nom_assure` VARCHAR(255), 
              ADD `prenom_assure` VARCHAR(255), 
              ADD `nom_jeune_fille_assure` VARCHAR(255), 
              ADD `taux_PEC` FLOAT, 
              ADD `numero_AT` INT(11), 
              ADD `AT_par_tiers` ENUM('0','1'), 
              ADD `fin_droits` DATE, 
              ADD `date_accident` DATE, 
              ADD `nom_employeur` VARCHAR(255), 
              ADD `adresse1_employeur` VARCHAR(255), 
              ADD `adresse2_employeur` VARCHAR(255), 
              ADD `ville_employeur` VARCHAR(255), 
              ADD `departement_employeur` VARCHAR(255), 
              ADD `cp_employeur` VARCHAR(255), 
              ADD `pays_employeur` VARCHAR(255), 
              ADD `date_debut_grossesse` DATE, 
              ADD `emetteur_id` VARCHAR(255) NOT NULL, 
              ADD `external_id` VARCHAR(255) NOT NULL;";
    $this->addQuery($query);
    $query = "CREATE TABLE `hprim21_complementaire` (
              `hprim21_complementaire_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              PRIMARY KEY (`hprim21_complementaire_id`)) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "ALTER TABLE `hprim21_complementaire`
              ADD `hprim21_patient_id` INT(11) UNSIGNED, 
              ADD `code_organisme` VARCHAR(255), 
              ADD `numero_adherent` VARCHAR(255), 
              ADD `debut_droits` DATE, 
              ADD `fin_droits` DATE, 
              ADD `type_contrat` VARCHAR(255), 
              ADD `emetteur_id` VARCHAR(255) NOT NULL, 
              ADD `external_id` VARCHAR(255) NOT NULL;";
    $this->addQuery($query);
    $query = "CREATE TABLE `hprim21_sejour` (
              `hprim21_sejour_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              PRIMARY KEY (`hprim21_sejour_id`)) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "ALTER TABLE `hprim21_sejour`
              ADD `hprim21_patient_id` INT(11) UNSIGNED NOT NULL, 
              ADD `hprim21_medecin_id` INT(11) UNSIGNED, 
              ADD `sejour_id` INT(11) UNSIGNED, 
              ADD `date_mouvement` DATETIME, 
              ADD `statut_admission` ENUM('OP','IP','IO','ER','MP','PA'), 
              ADD `localisation_lit` VARCHAR(255), 
              ADD `localisation_chambre` VARCHAR(255), 
              ADD `localisation_service` VARCHAR(255), 
              ADD `localisation4` VARCHAR(255), 
              ADD `localisation5` VARCHAR(255), 
              ADD `localisation6` VARCHAR(255), 
              ADD `localisation7` VARCHAR(255), 
              ADD `localisation8` VARCHAR(255), 
              ADD `emetteur_id` VARCHAR(255) NOT NULL, 
              ADD `external_id` VARCHAR(255) NOT NULL;";
    $this->addQuery($query);
    $query = "CREATE TABLE `hprim21_medecin` (
              `hprim21_medecin_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              PRIMARY KEY (`hprim21_medecin_id`)) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "ALTER TABLE `hprim21_medecin`
              ADD `user_id` INT(11) UNSIGNED, 
              ADD `nom` VARCHAR(255), 
              ADD `prenom` VARCHAR(255), 
              ADD `prenom2` VARCHAR(255), 
              ADD `alias` VARCHAR(255), 
              ADD `civilite` VARCHAR(255), 
              ADD `diplome` VARCHAR(255), 
              ADD `type_code` VARCHAR(255), 
              ADD `emetteur_id` VARCHAR(255) NOT NULL, 
              ADD `external_id` VARCHAR(255) NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.1");
    $query = "ALTER TABLE `hprim21_patient`
            ADD `nom_soundex2` VARCHAR(255) AFTER `nom_jeune_fille`, 
            ADD `prenom_soundex2` VARCHAR(255) AFTER `nom_soundex2`, 
            ADD `nomjf_soundex2` VARCHAR(255) AFTER `prenom_soundex2`, 
            CHANGE `naissance` `naissance` CHAR(10), 
            CHANGE `code_regime` `code_regime` MEDIUMINT(3) UNSIGNED ZEROFILL;";
    $this->addQuery($query);
    $query = "ALTER TABLE `hprim21_patient`
            ADD INDEX ( `patient_id` ),
            ADD INDEX ( `nom` ),
            ADD INDEX ( `prenom` ),
            ADD INDEX ( `prenom2` ),
            ADD INDEX ( `nom_jeune_fille` ),
            ADD INDEX ( `nom_soundex2` ),
            ADD INDEX ( `prenom_soundex2` ),
            ADD INDEX ( `nomjf_soundex2` ),
            ADD INDEX ( `naissance` ),
            ADD INDEX ( `sexe` ),
            ADD INDEX ( `emetteur_id` ),
            ADD INDEX ( `external_id` );";
    $this->addQuery($query);
    $query = "ALTER TABLE `hprim21_sejour`
            ADD INDEX ( `hprim21_patient_id` ),
            ADD INDEX ( `hprim21_medecin_id` ),
            ADD INDEX ( `sejour_id` ),
            ADD INDEX ( `date_mouvement` ),
            ADD INDEX ( `emetteur_id` ),
            ADD INDEX ( `external_id` );";
    $this->addQuery($query);
    $query = "ALTER TABLE `hprim21_medecin`
            ADD INDEX ( `user_id` ),
            ADD INDEX ( `nom` ),
            ADD INDEX ( `prenom` ),
            ADD INDEX ( `emetteur_id` ),
            ADD INDEX ( `external_id` );";
    $this->addQuery($query);
    $query = "ALTER TABLE `hprim21_complementaire`
            ADD INDEX ( `hprim21_patient_id` ),
            ADD INDEX ( `emetteur_id` ),
            ADD INDEX ( `external_id` );";
    $this->addQuery($query);


    $this->makeRevision("0.11");
    $query = "ALTER TABLE `hprim21_patient` 
              ADD INDEX (`date_derniere_modif`),
              ADD INDEX (`date_deces`),
              ADD INDEX (`debut_validite`),
              ADD INDEX (`fin_validite`),
              ADD INDEX (`fin_droits`),
              ADD INDEX (`date_accident`),
              ADD INDEX (`date_debut_grossesse`);";
    $this->addQuery($query);
    $query = "ALTER TABLE `hprim21_complementaire` 
              ADD INDEX (`debut_droits`),
              ADD INDEX (`fin_droits`);";
    $this->addQuery($query);

    $this->makeRevision("0.12");
    $query = "CREATE TABLE `echange_hprim21` (
              `echange_hprim21_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `group_id` INT (11) UNSIGNED NOT NULL,
              `date_production` DATETIME NOT NULL,
              `version` VARCHAR (255),
              `type` VARCHAR (255),
              `nom_fichier` VARCHAR (255),
              `id_emetteur` VARCHAR (255),
              `emetteur_desc` VARCHAR (255),
              `adresse_emetteur` VARCHAR (255),
              `id_destinataire` VARCHAR (255),
              `destinataire_desc` VARCHAR (255),
              `type_message` VARCHAR (255),
              `date_echange` DATETIME,
              `message` TEXT,
              `message_valide` ENUM ('0','1'),
              `id_permanent` VARCHAR (255),
              `object_id` INT (11) UNSIGNED,
              `object_class` ENUM ('CPatient','CSejour','CMediusers')
          ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `echange_hprim21` 
              ADD INDEX (`group_id`),
              ADD INDEX (`date_production`),
              ADD INDEX (`date_echange`),
              ADD INDEX (`object_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.13");
    $query = "ALTER TABLE `hprim21_complementaire` 
              ADD `echange_hprim21_id` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $query = "ALTER TABLE `hprim21_complementaire` 
              ADD INDEX (`echange_hprim21_id`)";
    $this->addQuery($query);

    $query = "ALTER TABLE `hprim21_sejour` 
              ADD `echange_hprim21_id` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $query = "ALTER TABLE `hprim21_sejour` 
              ADD INDEX (`echange_hprim21_id`)";
    $this->addQuery($query);

    $query = "ALTER TABLE `hprim21_patient` 
              ADD `echange_hprim21_id` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $query = "ALTER TABLE `hprim21_patient` 
              ADD INDEX (`echange_hprim21_id`)";
    $this->addQuery($query);

    $query = "ALTER TABLE `hprim21_medecin` 
              ADD `echange_hprim21_id` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $query = "ALTER TABLE `hprim21_medecin` 
              ADD INDEX (`echange_hprim21_id`)";
    $this->addQuery($query);

    $this->makeRevision("0.14");

    $query = "INSERT INTO content_tabular (`content`, `import_id`) 
              SELECT `message`, `echange_hprim21_id` FROM `echange_hprim21`;";
    $this->addQuery($query);

    $query = "ALTER TABLE `echange_hprim21` 
              DROP `message`;";
    $this->addQuery($query);

    $query = "ALTER TABLE `echange_hprim21` 
              ADD `message_content_id` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $query = "ALTER TABLE `echange_hprim21` 
              ADD INDEX (`message_content_id`);";
    $this->addQuery($query);

    $query = "UPDATE echange_hprim21 e 
              JOIN content_tabular cx ON echange_hprim21_id = cx.import_id
              SET  e.message_content_id = cx.content_id;";
    $this->addQuery($query);

    $query = "UPDATE `content_tabular`
             SET `import_id` = NULL,
                 `separator` = '|';";
    $this->addQuery($query);

    $this->makeRevision("0.15");

    $query = "CREATE TABLE `destinataire_hprim21` (
              `dest_hprim21_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `nom` VARCHAR (255) NOT NULL,
              `libelle` VARCHAR (255),
              `group_id` INT (11) UNSIGNED NOT NULL,
              `actif` ENUM ('0','1') NOT NULL DEFAULT '0',
              `message` ENUM ('L','C','R') DEFAULT 'C'
            ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `destinataire_hprim21` 
              ADD INDEX (`group_id`);";
    $this->addQuery($query);

    // Insertion de l'id du CDestinataireHprim21 dans le champ emetteur_id
    $query = "INSERT INTO destinataire_hprim21 (`nom`, `libelle`, `group_id`, `actif`, `message`) 
              SELECT DISTINCT `id_emetteur`, `emetteur_desc`, `group_id`, '1', `type` FROM `echange_hprim21`;";
    $this->addQuery($query);

    $query = "ALTER TABLE `echange_hprim21` 
              ADD `emetteur_id` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $query = "ALTER TABLE `echange_hprim21` 
              ADD INDEX (`emetteur_id`);";
    $this->addQuery($query);

    $query = "UPDATE echange_hprim21 e 
              JOIN destinataire_hprim21 dh ON id_emetteur = dh.nom
              SET  e.emetteur_id = dh.dest_hprim21_id;";
    $this->addQuery($query);

    $query = "ALTER TABLE `echange_hprim21` 
              DROP `id_emetteur`,
              DROP `emetteur_desc`,
              DROP `adresse_emetteur`,
              DROP `type`;";
    $this->addQuery($query);

    // Insertion de l'id du CDestinataireHprim21 dans le champ destinataire_id
    // Dans notre cas, toujours récepteur donc NULL
    $query = "ALTER TABLE `echange_hprim21` 
              ADD `destinataire_id` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $query = "ALTER TABLE `echange_hprim21` 
              ADD INDEX (`destinataire_id`);";
    $this->addQuery($query);

    $query = "ALTER TABLE `echange_hprim21` 
              DROP `id_destinataire`,
              DROP `destinataire_desc`;";
    $this->addQuery($query);

    $this->makeRevision("0.16");

    $query = "ALTER TABLE `echange_hprim21` 
              ADD `type` VARCHAR (255),
              ADD `acquittement_content_id` INT (11) UNSIGNED,
              ADD `statut_acquittement` VARCHAR (255),
              CHANGE `message_valide` `message_valide` ENUM ('0','1'),
              ADD `acquittement_valide` ENUM ('0','1'),
              CHANGE `object_class` `object_class` ENUM ('CPatient','CSejour','CMedecin');";
    $this->addQuery($query);

    $query = "ALTER TABLE `echange_hprim21` 
              ADD INDEX (`acquittement_content_id`);";
    $this->addQuery($query);

    $query = "UPDATE `echange_hprim21`
             SET `type` = 'C';";
    $this->addQuery($query);

    $this->makeRevision("0.17");

    $query = "ALTER TABLE `echange_hprim21` 
              CHANGE `type_message` `sous_type` VARCHAR( 255 )";
    $this->addQuery($query);

    $this->makeRevision("0.18");

    $query = "ALTER TABLE `echange_hprim21` 
                CHANGE `destinataire_id` `receiver_id` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $this->makeRevision("0.19");

    $query = "ALTER TABLE `echange_hprim21` 
                ADD `sender_id` INT (11) UNSIGNED AFTER `emetteur_id`,
                ADD `sender_class` ENUM ('CSenderFTP','CSenderSOAP') AFTER `sender_id`;";
    $this->addQuery($query);

    $query = "ALTER TABLE `echange_hprim21` 
                ADD INDEX (`sender_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.20");

    $query = "ALTER TABLE `echange_hprim21` 
                ADD `identifiant_emetteur` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.21");

    $query = "ALTER TABLE `echange_hprim21` 
                CHANGE `sender_class` `sender_class` ENUM ('CSenderFTP','CSenderSOAP','CSenderMLLP');";
    $this->addQuery($query);

    $this->makeRevision("0.22");

    $query = "ALTER TABLE `echange_hprim21` 
                CHANGE `sender_class` `sender_class` ENUM ('CSenderFTP','CSenderSOAP');";
    $this->addQuery($query);

    $this->makeRevision("0.23");

    $query = "ALTER TABLE `echange_hprim21` 
                CHANGE `sender_class` `sender_class` VARCHAR (80);";
    $this->addQuery($query);

    $this->makeRevision("0.24");

    $query = "ALTER TABLE `echange_hprim21` 
                ADD `reprocess` TINYINT (4) UNSIGNED DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.25");

    $query = "ALTER TABLE `destinataire_hprim21`
                ADD `OID` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.26");
    $query = "ALTER TABLE `destinataire_hprim21`
                ADD `synchronous` ENUM ('0','1') NOT NULL DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.27");

    $query = "ALTER TABLE `destinataire_hprim21`
                ADD `monitor_sources` ENUM ('0','1') NOT NULL DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.28");
    $query = "ALTER TABLE `echange_hprim21`
                ADD `master_idex_missing` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.29");
    $query = "ALTER TABLE `destinataire_hprim21`
                CHANGE `actif` `actif` ENUM ('0','1') NOT NULL DEFAULT '1',
                ADD `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'prod';";
    $this->addQuery($query);

    $this->makeRevision("0.30");

    $query = "ALTER TABLE `destinataire_hprim21`
                ADD `exchange_format_delayed` SMALLINT (4) UNSIGNED DEFAULT '60';";
    $this->addQuery($query);

    $this->makeRevision("0.31");
    $query = "ALTER TABLE `echange_hprim21`
                ADD INDEX( `receiver_id`, `date_echange`),
                ADD INDEX( `sender_id`, `sender_class`, `date_echange`);";
    $this->addQuery($query);

    $this->makeRevision("0.32");

    $query = "ALTER TABLE `echange_hprim21`
                ADD `emptied` ENUM('0', '1') NOT NULL DEFAULT '0';";
    $this->addQuery($query);

    $query = "UPDATE `echange_hprim21`
                SET `emptied` = '1'
                WHERE `message_content_id` IS NULL
                AND `acquittement_content_id` IS NULL";
    $this->addQuery($query);

    $query = "ALTER TABLE `echange_hprim21`
                ADD INDEX `emptied_production` (`emptied`, `date_production`);";
    $this->addQuery($query);

    if($this->tableExists('source_ftp')){
      $this->makeRevision("0.33");
      $this->addMethod("addingGroupSourceOscour");
    }else{
      $this->makeEmptyRevision("0.33");
    }

    $this->makeRevision("0.34");

    $query = "ALTER TABLE `echange_hprim21`
                ADD `response_datetime` DATETIME;";
    $this->addQuery($query);

    $this->makeRevision("0.35");

    $query = "ALTER TABLE `echange_hprim21`
                CHANGE `date_echange` `send_datetime` DATETIME;";
    $this->addQuery($query);

    $this->makeRevision("0.36");

    $query = "ALTER TABLE `echange_hprim21`
                ADD INDEX `ots` (`object_class`, `type`, `sous_type`);";
    $this->addQuery($query);

    $this->makeRevision("0.37");

    $query = "ALTER TABLE `destinataire_hprim21`
                ADD `type` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.38");

    $query = "ALTER TABLE `destinataire_hprim21`
                ADD `use_specific_handler` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.39");
    $this->setModuleCategory("interoperabilite", "echange");

    $this->mod_version = "0.40";
  }
}
