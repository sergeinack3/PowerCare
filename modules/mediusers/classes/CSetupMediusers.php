<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers;

use Ox\Core\CAppUI;
use Ox\Core\CSetup;
use Ox\Mediboard\CompteRendu\CSetupCompteRendu;

/**
 * @codeCoverageIgnore
 */
class CSetupMediusers extends CSetup {
  /**
   * Update functions named "Cabinets" to type = 'cabinet'
   *
   * @return bool
   */
  protected function updateFct(){
    $ds = $this->ds;

    if ($ds->loadTable("groups_mediboard")) {
      $query = "UPDATE `functions_mediboard`, `groups_mediboard`
               SET `functions_mediboard`.`type` = 'cabinet'
               WHERE `functions_mediboard`.`group_id` = `groups_mediboard`.`group_id`
               AND `groups_mediboard`.`text` = 'Cabinets'";
      $ds->exec($query);
      $ds->error();
    }
    return true;
  }

  function __construct() {
    parent::__construct();

    $this->mod_name = "mediusers";

    $this->makeRevision("0.0");
    $query = "CREATE TABLE `users_mediboard` (
               `user_id` INT(11) UNSIGNED NOT NULL,
               `function_id` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
               PRIMARY KEY (`user_id`)
             ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "CREATE TABLE `functions_mediboard` (
               `function_id` TINYINT(4) UNSIGNED NOT NULL AUTO_INCREMENT,
               `group_id` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
               `text` VARCHAR(50) NOT NULL,
               `color` VARCHAR(6) NOT NULL DEFAULT 'ffffff',
               PRIMARY KEY (`function_id`)
             ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    /* Begin data for PHPUnit */
    $this->makeRevision("0.0.1");
    $query = "INSERT INTO `functions_mediboard` (`function_id`, `group_id`, `text`, `color`) VALUES (1, 1, 'OX', '3d85c6');";
    $this->addQuery($query);

    $this->makeRevision("0.0.2");
    $query = "INSERT INTO `users_mediboard` (`user_id`, `function_id`) VALUES (14, 1);";
    $this->addQuery($query);
    /* End data for PHPUnit */

    $this->makeRevision("0.1");
    $query = "ALTER TABLE `users_mediboard` ADD `remote` TINYINT DEFAULT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.11");
    $query = "ALTER TABLE `users_mediboard` ADD `adeli` int(9) DEFAULT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.12");
    $query = "ALTER TABLE `users_mediboard` CHANGE `adeli` `adeli` VARCHAR(9);";
    $this->addQuery($query);

    $this->makeRevision("0.13");
    $query = "CREATE TABLE `discipline` (
                `discipline_id` TINYINT(4) UNSIGNED NOT NULL AUTO_INCREMENT,
                `text` VARCHAR(100) NOT NULL,
                PRIMARY KEY (`discipline_id`)
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "ALTER TABLE `users_mediboard` ADD `discipline_id` TINYINT(4) DEFAULT NULL AFTER `function_id`";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('ADDICTOLOGIE CLINIQUE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('AIDE MEDICALE URGENTE OU MEDECINE D\'URGENCE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('ALLERGOLOGIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('ANATOMIE ET CYTOLOGIE PATHOLOGIQUES');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('ANDROLOGIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('ANESTHESIE-REANIMATION');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('ANGIOLOGIE/ MEDECINE VASCULAIRE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('BIOLOGIE MEDICALE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('CANCEROLOGIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('CARDIOLOGIE / PATHOLOGIE CARDIO-VASCULAIRE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE DE LA FACE ET DU COU');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE GENERALE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE INFANTILE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE MAXILLO-FACIALE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE MAXILLO-FACIALE ET STOMATOLOGIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE ORTHOPEDIQUE ET TRAUMATOLOGIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE PLASTIQUE, RECONSTRUCTRICE ET ESTHETIQUE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE THORACIQUE ET CARDIO-VASCULAIRE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE UROLOGIQUE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE VASCULAIRE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('CHIRURGIE VISCERALE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('DERMATOLOGIE ET VENEREOLOGIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('ENDOCRINOLOGIE ET METABOLISMES');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('EVALUATION ET TRAITEMENT DE LA DOULEUR');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('GASTRO-ENTEROLOGIE ET HEPATOLOGIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('GENETIQUE MEDICALE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('GERIATRIE / GERONTOLOGIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('GYNECOLOGIE MEDICALE, OBSTETRIQUE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('GYNECOLOGIE-OBSTETRIQUE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('HEMATOLOGIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('HEMOBIOLOGIE-TRANSFUSION / TECHNOLOGIE TRANSFUSION');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('HYDROLOGIE ET CLIMATOLOGIE MEDICALE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('IMMUNOLOGIE ET IMMUNOPATHOLOGIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE AEROSPATIALE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE DE CATASTROPHE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE DE LA REPRODUCTION ET GYNECOLOGIE MEDICAL');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE DU TRAVAIL');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE ET BIOLOGIE DU SPORT');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE GENERALE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE INTERNE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE LEGALE ET EXPERTISES MEDICALES');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE NUCLEAIRE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE PENITENTIAIRE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('MEDECINE PHYSIQUE ET DE READAPTATION');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('NEPHROLOGIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('NEUROCHIRURGIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('NEUROLOGIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('NEUROPSYCHIATRIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('NUTRITION');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('ONCOLOGIE MEDICALE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('ONCOLOGIE OPTION RADIOTHERAPIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('OPHTALMOLOGIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('ORTHOPEDIE DENTO-MAXILLO-FACIALE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('OTO-RHINO-LARYNGOLOGIE ET CHIRURGIE CERVICO-FACIALE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('PATHOLOGIE INFECTIEUSE ET TROPICALE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('PEDIATRIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('PHONIATRIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('PNEUMOLOGIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('PSYCHIATRIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('PSYCHIATRIE DE L\'ENFANT ET DE L\'ADOLESCENT');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('RADIO-DIAGNOSTIC ET IMAGERIE MEDICALE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('RADIOTHERAPIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('REANIMATION MEDICALE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('RECHERCHE MEDICALE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('RHUMATOLOGIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('SANTE PUBLIQUE ET MEDECIN SOCIALE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('STOMATOLOGIE');";
    $this->addQuery($query);
    $query = "INSERT INTO `discipline` (`text`) VALUES('TOXICOMANIES ET ALCOOLOGIE');";
    $this->addQuery($query);

    $this->makeRevision("0.14");
    $query = "ALTER TABLE `functions_mediboard`
              ADD `type` ENUM('administratif', 'cabinet') DEFAULT 'administratif' NOT NULL AFTER `group_id`;";
    $this->addQuery($query);

    $this->addMethod("updateFct");

    $this->makeRevision("0.15");
    $query = "ALTER TABLE `functions_mediboard` ADD INDEX ( `group_id` ) ;";
    $this->addQuery($query);
    $query = "ALTER TABLE `users_mediboard` ADD INDEX ( `function_id` ) ;";
    $this->addQuery($query);
    $query = "ALTER TABLE `users_mediboard` ADD INDEX ( `discipline_id` ) ;";
    $this->addQuery($query);

    $this->makeRevision("0.16");
    $query = "ALTER TABLE `discipline` 
                CHANGE `discipline_id` `discipline_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `text` `text` varchar(255) NOT NULL;";
    $this->addQuery($query);
    $query = "ALTER TABLE `functions_mediboard` 
                CHANGE `function_id` `function_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `group_id` `group_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `text` `text` varchar(255) NOT NULL;";
    $this->addQuery($query);
    $query = "ALTER TABLE `users_mediboard` 
                CHANGE `user_id` `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `function_id` `function_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `adeli` `adeli` int(9) unsigned zerofill NULL,
                CHANGE `remote` `remote` enum('0','1') NULL,
                CHANGE `discipline_id` `discipline_id` int(11) unsigned NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.17");
    $query = "ALTER TABLE `users_mediboard` 
                ADD `commentaires`  text NULL,
                ADD `actif` enum('0','1') NOT NULL DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.18");
    $query = "ALTER TABLE `users_mediboard` 
                ADD `deb_activite` datetime NULL,
                ADD `fin_activite` datetime NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.19");
    $query = "ALTER TABLE `users_mediboard` 
                CHANGE `deb_activite` `deb_activite` date NULL,
                CHANGE `fin_activite` `fin_activite` date NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.20");
    $query = "CREATE TABLE `spec_cpam` (
               `spec_cpam_id` TINYINT(4) UNSIGNED NOT NULL,
               `text` VARCHAR(255) NOT NULL,
               `actes` VARCHAR(255) NOT NULL,
               PRIMARY KEY (`spec_cpam_id`)
               ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "ALTER TABLE `users_mediboard` ADD `spec_cpam_id` TINYINT(4) DEFAULT NULL AFTER `discipline_id`";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(1,'MEDECINE GENERALE','C|K|Z|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(2,'ANESTHESIOLOGIE - REANIMATION CHIRURGICALE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(3,'PATHOLOGIE CARDIO-VASCULAIRE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(4,'CHIRURGIE GENERALE','CS|K|KC|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(5,'DERMATOLOGIE ET VENEROLOGIE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(6,'RADIODIAGNOSTIC ET IMAGERIE MEDICALE','C|K|Z|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(7,'GYNECOLOGIE OBSTETRIQUE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(8,'GASTRO-ENTEROLOGIE ET HEPATOLOGIE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(9,'MEDECINE INTERNE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(10,'NEUROCHIRURGIEN','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(11,'OTO RHINO LARYNGOLOGISTE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(12,'PEDIATRE','CS|K|FPE|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(13,'PNEUMOLOGIE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(14,'RHUMATOLOGIE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(15,'OPHTAMOLOGIE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(16,'CHIRURGIE UROLOGIQUE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(17,'NEURO PSYCHIATRIE','CNP');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(18,'STOMATOLOGIE','CS|Z|SCM|PRO|ORT|K|FDA|FDC|FDO|FDR|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(19,'CHIRURGIE DENTAIRE','C|Z|D|DC|SPR|SC|FDA|FDC|FDO|FDR');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(21,'SAGE FEMME','C|SF|SFI');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(24,'INFIRMIER','AMI');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(26,'MASSEUR KINESITHERAPEUTE','AMC');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(27,'PEDICURE','AMP');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(28,'ORTHOPHONISTE','AMO');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(29,'ORTHOPTISTE','AMY');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(30,'LABORATOIRE D\'ANALYSES MEDICALES','B|KB');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(31,'MEDECINE PHYSIQUE ET DE READAPTATION','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(32,'NEUROLOGIE','CNP|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(33,'PSYCHIATRIE GENERALE','CNP');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(35,'NEPHROLOGIE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(36,'CHIRURGIE DENTAIRE (Spéc. O.D.F.)','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(37,'ANATOMIE-CYTOLOGIE-PATHOLOGIQUES','CS|P');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(38,'MEDECIN BIOLOGISTE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(39,'LABORATOIRE POLYVALENT','B');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(40,'LABORATOIRE ANATOMO-PATHOLOGISTE','B');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(41,'CHIRURGIE ORTHOPEDIQUE et TRAUMATOLOGIE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(42,'ENDOCRINOLOGIE et METABOLISMES','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(43,'CHIRURGIE INFANTILE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(44,'CHIRURGIE MAXILLO-FACIALE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(45,'CHIRURGIE MAXILLO-FACIALE ET STOMATOLOGIE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(46,'CHIRURGIE PLASTIQUE RECONSTRUCTRICE ET ESTHECS','K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(47,'CHIRURGIE THORACIQUE ET CARDIO-VASCULAIRE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(48,'CHIRURGIE VASCULAIRE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(49,'CHIRURGIE VISCERALE ET DIGESTIVE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(50,'PHARMACIEN','');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(70,'GYNECOLOGIE MEDICALE','CS|K|ZM|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(71,'HEMATOLOGIE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(72,'MEDECINE NUCLEAIRE','CS|K|ZN|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(73,'ONCOLOGIE MEDICALE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(74,'ONCOLOGIE RADIOTHERAPIQUE','CS|K|Z|ZN|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(75,'PSYCHIATRIE DE L''ENFANT ET DE L''ADOLESCENT','CNP');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(76,'RADIOTHERAPIE','CS|Z|ZN|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(77,'OBSTETRIQUE','CS|K|Z|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`)
              VALUES(78,'GENETIQUE MEDICALE','CS|K|ADE|ADI|ADC|ACO|ADA|ATM');";
    $this->addQuery($query);

    $this->makeRevision("0.21");
    $query = "ALTER TABLE `users_mediboard` CHANGE `spec_cpam_id` `spec_cpam_id` int(11) unsigned NULL;";
    $this->addQuery($query);
    $query = "ALTER TABLE `spec_cpam` CHANGE `spec_cpam_id` `spec_cpam_id` int(11) unsigned NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.22");
    $query = "ALTER TABLE `users_mediboard` ADD `titres`  text NULL AFTER adeli;";
    $this->addQuery($query);

    $this->makeRevision("0.23");
    $query = "ALTER TABLE `functions_mediboard` 
                ADD `adresse` TEXT NULL,
                ADD `cp` int(5) unsigned zerofill NULL,
                ADD `ville` VARCHAR( 50 ) NULL,
                ADD `tel` bigint(10) unsigned zerofill NULL,
                ADD `fax` bigint(10) unsigned zerofill NULL,
                ADD `soustitre` TEXT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.24");
    $query = "ALTER TABLE `discipline` ADD `categorie` enum('ORT','ORL','OPH','DER','STO','GAS','ARE','RAD','GYN','EST') NULL";
    $this->addQuery($query);

    $this->makeRevision("0.25");
    $query = "UPDATE `users_mediboard` SET `discipline_id` = NULL WHERE `discipline_id` = '0';";
    $this->addQuery($query);

    $this->makeRevision("0.26");
    $query = "ALTER TABLE `users_mediboard` ADD `compte` VARCHAR(23);";
    $this->addQuery($query);

    $this->makeRevision("0.27");
    $query = "ALTER TABLE `users_mediboard` ADD `banque_id` INT(11) UNSIGNED;";
    $this->addQuery($query);

    $this->makeRevision("0.28");
    $query = "ALTER TABLE `functions_mediboard` ADD `compta_partagee` BOOL NOT NULL DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.29");
    $query = "CREATE TABLE `secondary_function` (
               `secondary_function_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
               `function_id` INT (11) UNSIGNED NOT NULL,
               `user_id` INT (11) UNSIGNED NOT NULL
             ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "ALTER TABLE `secondary_function`
              ADD INDEX (`function_id`),
              ADD INDEX (`user_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.30");
    $query = "ALTER TABLE `users_mediboard`
              ADD `rpps` BIGINT (11) UNSIGNED ZEROFILL AFTER `adeli`;";
    $this->addQuery($query);
    $query = "ALTER TABLE `users_mediboard`
              ADD INDEX (`deb_activite`),
              ADD INDEX (`fin_activite`),
              ADD INDEX (`banque_id`),
              ADD INDEX (`spec_cpam_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.31");
    $query = "INSERT INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`) VALUES(80,'SANTE PUBLIQUE ET MEDECINE SOCIALE','');";
    $this->addQuery($query);

    $this->makeRevision("0.32");
    $query = "ALTER TABLE `users_mediboard` ADD `code_intervenant_cdarr` CHAR (2) DEFAULT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.33");
    $query = "ALTER TABLE `functions_mediboard`
              ADD `actif` ENUM ('0','1') DEFAULT '1',
              ADD `admission_auto` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.34");
    $query = "ALTER TABLE `functions_mediboard`
              ADD `consults_partagees` ENUM ('0','1') NOT NULL DEFAULT '1' AFTER compta_partagee;";
    $this->addQuery($query);

    $this->makeRevision("0.35");
    $query = "ALTER TABLE `users_mediboard`
              ADD `secteur` ENUM ('1','2'),
              ADD `cab` VARCHAR (255),
              ADD `conv` VARCHAR (255),
              ADD `zisd` VARCHAR (255),
              ADD `ik` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.36");
    $query = "ALTER TABLE `users_mediboard`
              ADD `cps` BIGINT ZEROFILL AFTER `rpps`;";
    $this->addQuery($query);

    $this->makeRevision("0.37");
    $query = "ALTER TABLE `users_mediboard`
              CHANGE `cps` `cps` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.38");
    $query = "ALTER TABLE `functions_mediboard`
              ADD `quotas` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $this->makeRevision("0.39");
    $query = "ALTER TABLE `functions_mediboard`
              CHANGE `tel` `tel` VARCHAR (20),
              CHANGE `fax` `fax` VARCHAR (20)";
    $this->addQuery($query);

    $this->makeRevision("0.40");

    $query = "ALTER TABLE `users_mediboard`
               ADD `ean` VARCHAR (30),
               ADD `rcc` VARCHAR (30),
               ADD `adherent` VARCHAR (30);";
    $this->addQuery($query);

    $this->makeRevision("0.41");

    $query = "ALTER TABLE `users_mediboard`
               ADD `mail_apicrypt` VARCHAR (50);";
    $this->addQuery($query);

    $this->makeRevision("0.42");

    $query = "ALTER TABLE `users_mediboard`
               ADD `compta_deleguee` BOOL NOT NULL DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.43");

    $query = "ALTER TABLE `users_mediboard`
               ADD `num_astreinte` VARCHAR (20)";
    $this->addQuery($query);

    $this->makeRevision("0.44");

    $query = "ALTER TABLE `users_mediboard`
               DROP `num_astreinte`";
    $this->addQuery($query);

    $this->makeRevision("0.45");

    $query = "ALTER TABLE `functions_mediboard`
               ADD `facturable` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.46");

    $query = "ALTER TABLE `users_mediboard`
            ADD `initials` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.47");

    $query = "ALTER TABLE `users_mediboard`
            ADD `debut_bvr` VARCHAR (10);";
    $this->addQuery($query);

    $this->makeRevision("0.48");

    $this->makeRevision("0.49");

    $query = CSetupCompteRendu::renameTemplateFieldQuery("Praticien - Spécialité", "Praticien - Discipline");
    $this->addQuery($query, true);

    $this->makeRevision("0.50");
    $query = "ALTER TABLE `users_mediboard`
              ADD `last_ldap_checkout` DATE;";
    $this->addQuery($query);

    $this->makeRevision("0.51");
    $query = "ALTER TABLE `users_mediboard`
              ADD `service_account` ENUM ('0','1') NOT NULL DEFAULT '0' AFTER `actif`;";
    $this->addQuery($query);

    $this->makeRevision("0.52");
    $query = "ALTER TABLE `users_mediboard`
              DROP `service_account`;";
    $this->addQuery($query);

    $this->makeRevision("0.53");
    $query = "ALTER TABLE `users_mediboard`
              ADD `other_specialty_id` INT (11) UNSIGNED,
              ADD INDEX (`other_specialty_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.54");
    $query = "ALTER TABLE `users_mediboard`
              ADD `color` VARCHAR (6);";
    $this->addQuery($query);

    $this->makeRevision("0.55");
    $query = "ALTER TABLE `users_mediboard`
                ADD `use_bris_de_glace` ENUM ('0','1') NOT NULL DEFAULT '0' AFTER `actif`;";
    $this->addQuery($query);
    
    $this->makeRevision('0.56');

    $query = "ALTER TABLE `users_mediboard`
                ADD `contrat_acces_soins` ENUM('0', '1'),
                ADD `option_coordination` ENUM('0', '1');";
    $this->addQuery($query);

    $query = "INSERT IGNORE INTO `spec_cpam` (`spec_cpam_id`, `text`, `actes`) VALUES
                (20, 'Réanimation médicale', ''),
                (34, 'Gériatrie', ''),
                (51, 'Pharmacien mutualiste', ''),
                (53, 'Chirurgie dentaire orale', ''),
                (78, 'Génétique médicale', ''),
                (79, 'Gynécologie obstétrique et gynécologie médicale', '');";
    $this->addQuery($query);

    $this->makeRevision('0.57');

    $query = "ALTER TABLE `users_mediboard`
              ADD `inami` BIGINT (11) UNSIGNED ZEROFILL AFTER `rpps`;";
    $this->addQuery($query);
    $this->makeRevision('0.58');

    $query = "ALTER TABLE `users_mediboard`
                ADD `electronic_bill` ENUM ('0','1') DEFAULT '0',
                ADD `specialite_tarmed` MEDIUMINT (4) UNSIGNED ZEROFILL;";
    $this->addQuery($query);
    $this->makeRevision('0.59');

    $query = "ALTER TABLE `users_mediboard`
                ADD `ean_base` VARCHAR (255),
                ADD `role_tarmed` VARCHAR (255),
                ADD `place_tarmed` VARCHAR (255);";
    $this->addQuery($query);
    $this->makeRevision('0.60');

    $query = "ALTER TABLE `users_mediboard`
                ADD `reminder_text` TEXT;";
    $this->addQuery($query);

    $this->makeRevision("0.61");
    $query = "ALTER TABLE `functions_mediboard`
              ADD `initials` VARCHAR(255) AFTER `text`;";
    $this->addQuery($query);

    $this->makeRevision('0.62');

    $query = "ALTER TABLE `users_mediboard`
               ADD `mssante_address` VARCHAR (100);";
    $this->addQuery($query);

    $this->makeRevision('0.63');

    $query = "ALTER TABLE `users_mediboard`
               ADD `ofac_id` VARCHAR (20);";
    $this->addQuery($query);

    $this->makeRevision('0.64');

    $query = "ALTER TABLE `users_mediboard`
               ADD `mode_tp_acs` ENUM ('tp_coordonne', 'amc_standard');";
    $this->addQuery($query);

    $this->makeRevision('0.65');
    $query = "ALTER TABLE `functions_mediboard`
              ADD `siret` VARCHAR(255) AFTER `fax`;";
    $this->addQuery($query);
    $this->makeRevision('0.66');

    $query = "ALTER TABLE `users_mediboard`
                ADD `use_cdm` ENUM ('0','1') DEFAULT '0',
                ADD `login_cdm` VARCHAR (20),
                ADD `mdp_cdm` VARCHAR (64);";
    $this->addQuery($query);
    $this->makeRevision('0.67');

    $query = "ALTER TABLE `users_mediboard` 
      ADD `sexe` ENUM ('u','f','m') DEFAULT 'u' AFTER `spec_cpam_id`;";
    $this->addQuery($query);
    $this->makeRevision('0.68');

    $query = "ALTER TABLE `users_mediboard` 
      DROP `sexe`";
    $this->addQuery($query);

    $this->makeRevision('0.69');

    $query = "ALTER TABLE `users_mediboard`
      ADD `pratique_tarifaire` ENUM ('none', 'optam', 'optamco') DEFAULT 'none' AFTER `secteur`;";
    $this->addQuery($query);

    $query = "UPDATE `users_mediboard` SET `pratique_tarifaire` = 'optam' WHERE `contrat_acces_soins` = '1';";
    $this->addQuery($query);

    $query = "ALTER TABLE `users_mediboard`
      DROP `contrat_acces_soins`,
      DROP `option_coordination`;";
    $this->addQuery($query);
    $this->makeRevision('0.70');

    $query = "ALTER TABLE `users_mediboard`
                CHANGE `compta_deleguee` `compta_deleguee` ENUM ('0','1','with_prat') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.71");
    $query = "ALTER TABLE `functions_mediboard`
                ADD `email` VARCHAR (50) AFTER `fax`;";
    $this->addQuery($query);

    $this->makeRevision("0.72");
    $query = "ALTER TABLE `functions_mediboard`
      ADD `create_sejour_consult` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.73");
    $query = "ALTER TABLE `users_mediboard`
      ADD `activite` ENUM ('liberale','salarie','mixte') DEFAULT 'liberale' AFTER `use_bris_de_glace`;";
    $this->addQuery($query);

    if (CAppUI::gconf("dPcabinet CConsultation create_consult_sejour")) {
      $query = "UPDATE `users_mediboard`
        SET `activite` = 'salarie'";
      $this->addQuery($query);
    }
    $this->makeRevision('0.74');

    $query = "ALTER TABLE `users_mediboard` CHANGE `secteur` `secteur` ENUM('1', '1dp', '2', 'nc');";
    $this->addQuery($query);
    $this->makeRevision('0.75');

    $query = "ALTER TABLE `functions_mediboard`
                ADD `ean` VARCHAR (255),
                ADD `rcc` VARCHAR (255);";
    $this->addQuery($query);
    $this->makeRevision('0.76');

    $query = "ALTER TABLE `users_mediboard` ADD `main_user_id` INT (11) UNSIGNED AFTER `discipline_id`;";
    $this->addQuery($query);
    $this->makeRevision('0.77');

    $query = "ALTER TABLE `users_mediboard` ADD `ccam_context` INT (2) UNSIGNED;";
    $this->addQuery($query);
    $this->makeRevision('0.78');

    $query = "ALTER TABLE `functions_mediboard` CHANGE `cp` `cp` VARCHAR (5);";
    $this->addQuery($query);
    $this->makeRevision('0.79');

    $query = "ALTER TABLE `users_mediboard` 
                ADD `num_contrat_prive` VARCHAR (64);";
    $this->addQuery($query);

    $this->makeRevision("0.80");

    $query = "ALTER TABLE `users_mediboard`
      ADD `alert_bmr_bhre` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);
    $this->makeRevision("0.81");

    $query = "ALTER TABLE `users_mediboard`
      DROP `alert_bmr_bhre`";
    $this->addQuery($query);
    $this->makeRevision("0.82");

    $query = "ALTER TABLE `functions_mediboard` 
                ADD `finess` INT (9) UNSIGNED ZEROFILL;";
    $this->addQuery($query);
    $this->makeRevision("0.83");

    $query = "ALTER TABLE `users_mediboard` 
                ADD `compte_ch_id` INT (11) UNSIGNED,
                ADD INDEX (`compte_ch_id`);";
    $this->addQuery($query);

    $query = "CREATE TABLE `compte_ch` (
                `compte_ch_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name` VARCHAR (255) NOT NULL,
                `user_id` INT (11) UNSIGNED NOT NULL,
                `rcc` VARCHAR (255),
                `adherent` VARCHAR (255),
                `debut_bvr` VARCHAR (10),
                `banque_id` INT (11) UNSIGNED,
                INDEX (`user_id`),
                INDEX (`banque_id`)
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "INSERT INTO `compte_ch` (`name`, `user_id`, `rcc`, `adherent`, `debut_bvr`, `banque_id`)
              SELECT 'Compte 1', users_mediboard.user_id, users_mediboard.rcc, users_mediboard.adherent, users_mediboard.debut_bvr, users_mediboard.banque_id
              FROM users_mediboard
              WHERE (users_mediboard.rcc IS NOT NULL AND users_mediboard.rcc != '')
              OR users_mediboard.adherent IS NOT NULL
              OR users_mediboard.debut_bvr IS NOT NULL";
    $this->addQuery($query);

    $query = "UPDATE `users_mediboard`, `compte_ch`
      SET users_mediboard.compte_ch_id = compte_ch.compte_ch_id
      WHERE compte_ch.user_id = users_mediboard.user_id;";
    $this->addQuery($query);
    $this->makeRevision('0.84');

    $query = "ALTER TABLE `users_mediboard` ADD `destinataire_favori` VARCHAR (50) DEFAULT NULL;";
    $this->addQuery($query);

    $this->makeRevision('0.85');

    $query = "ALTER TABLE `users_mediboard` ADD `nom_destinataire_favori` VARCHAR (50) DEFAULT NULL;";
    $this->addQuery($query);

    $this->makeRevision('0.86');

    $query = "ALTER TABLE `users_mediboard`
      CHANGE `pratique_tarifaire` `pratique_tarifaire` ENUM ('none', 'optam', 'optamco');";
    $this->addQuery($query);

    $this->makeRevision('0.87');

    $query = "ALTER TABLE `users_mediboard`     ADD `ean_xml_factu` VARCHAR (30);";
    $this->addQuery($query);
    $query = "ALTER TABLE `functions_mediboard` ADD `ean_xml_factu` VARCHAR (30);";
    $this->addQuery($query);

    $this->makeRevision('0.88');

    $query = "DROP TABLE spec_cpam;";
    $this->addQuery($query);

    $this->makeRevision("0.89");

    $query = "ALTER TABLE `users_mediboard` ADD `astreinte` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.90");

    $this->addDefaultConfig("mediusers CMediusers tag_mediuser", "mediusers tag_mediuser");
    $this->addDefaultConfig("mediusers CMediusers tag_mediuser_software", "mediusers tag_mediuser_software");

    $this->makeRevision("0.91");

    $query = "ALTER TABLE `users_mediboard`
                ADD INDEX (`main_user_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.92");
    $this->setModuleCategory("parametrage", "metier");
    $this->makeRevision("0.93");

    $query = "ALTER TABLE `users_mediboard`
               DROP `rcc`,
               DROP `adherent`,
               DROP `debut_bvr`;";
    $this->addQuery($query);

    $this->makeRevision("0.94");
    $query = "ALTER TABLE `functions_mediboard`
              CHANGE `consults_partagees` `consults_events_partagees` ENUM ('0','1') NOT NULL DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.95");
    $query = "ALTER TABLE `users_mediboard` ADD `sub_psc` VARCHAR (120);";
      $this->addQuery($query);

    $this->makeRevision('0.96');

    $this->addQuery(
        'ALTER TABLE `users_mediboard`
         DROP COLUMN `compte_ch_id`,
         DROP COLUMN `electronic_bill`,
         DROP COLUMN `specialite_tarmed`,
         DROP COLUMN `role_tarmed`,
         DROP COLUMN `place_tarmed`;'
    );

    $this->addQuery('ALTER TABLE `functions_mediboard` DROP `ean_xml_factu`;');

    $this->addQuery("DROP TABLE `compte_ch`");

    $this->mod_version = "0.97";
  }
}
