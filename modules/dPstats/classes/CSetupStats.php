<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stats;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupStats extends CSetup {

  function __construct() {
    parent::__construct();

    $this->mod_name = "dPstats";

    $this->makeRevision("0.0");

    $this->makeRevision("0.1");
    $query = "CREATE TABLE `temps_op` (
               `temps_op_id` INT(11) NOT NULL AUTO_INCREMENT ,
               `chir_id` INT(11) NOT NULL ,
               `ccam` VARCHAR( 100 ) NOT NULL ,
               `nb_intervention` INT(11) NOT NULL ,
               `estimation` TIME NOT NULL ,
               `occup_moy` TIME NOT NULL ,
               `occup_ecart` TIME NOT NULL ,
               `duree_moy` TIME NOT NULL ,
               `duree_ecart` TIME NOT NULL,
               PRIMARY KEY  (temps_op_id)
               ) /*! ENGINE=MyISAM */ COMMENT='Table temporaire des temps operatoire';";
    $this->addQuery($query);
    $query = "CREATE TABLE `temps_prepa` (
               `temps_prepa_id` INT(11) NOT NULL AUTO_INCREMENT ,
               `chir_id` INT(11) NOT NULL ,
               `nb_prepa` INT(11) NOT NULL ,
               `nb_plages` INT(11) NOT NULL ,
               `duree_moy` TIME NOT NULL ,
               `duree_ecart` TIME NOT NULL,
               PRIMARY KEY  (temps_prepa_id)
               ) /*! ENGINE=MyISAM */ COMMENT='Table temporaire des temps preparatoire';";
    $this->addQuery($query);

    $this->makeRevision("0.11");
    $query = "ALTER TABLE `temps_op` ADD INDEX ( `chir_id` );";
    $this->addQuery($query);
    $query = "ALTER TABLE `temps_op` ADD INDEX ( `ccam` );";
    $this->addQuery($query);
    $query = "ALTER TABLE `temps_prepa` ADD INDEX ( `chir_id` );";
    $this->addQuery($query);

    $this->makeRevision("0.12");
    $query = "CREATE TABLE `temps_hospi` (
               `temps_hospi_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
               `praticien_id` INT(11) UNSIGNED NOT NULL,
               `ccam` VARCHAR( 100 ) NOT NULL,
               `type` ENUM('ambu','comp') DEFAULT 'ambu' NOT NULL,
               `nb_sejour` INT(11) UNSIGNED DEFAULT NULL,
               `duree_moy` FLOAT UNSIGNED DEFAULT NULL,
               `duree_ecart` FLOAT UNSIGNED DEFAULT NULL,
               PRIMARY KEY (`temps_hospi_id`),
               INDEX (`praticien_id`),
               INDEX (`ccam`)
               ) /*! ENGINE=MyISAM */ COMMENT='Table temporaire des temps d\'hospitalisation';";
    $this->addQuery($query);

    $this->makeRevision("0.13");
    $query = "ALTER TABLE `temps_hospi` CHANGE `ccam` `ccam` varchar(255) NOT NULL;";
    $this->addQuery($query);
    $query = "ALTER TABLE `temps_op` " .
      "\nCHANGE `temps_op_id` `temps_op_id` int(11) unsigned NOT NULL AUTO_INCREMENT," .
      "\nCHANGE `chir_id` `chir_id` int(11) unsigned NOT NULL DEFAULT '0'," .
      "\nCHANGE `ccam` `ccam` varchar(255) NOT NULL," .
      "\nCHANGE `nb_intervention` `nb_intervention` int(11) unsigned NOT NULL DEFAULT '0';";
    $this->addQuery($query);
    $query = "ALTER TABLE `temps_prepa` " .
      "\nCHANGE `temps_prepa_id` `temps_prepa_id` int(11) unsigned NOT NULL AUTO_INCREMENT," .
      "\nCHANGE `chir_id` `chir_id` int(11) unsigned NOT NULL DEFAULT '0'," .
      "\nCHANGE `nb_prepa` `nb_prepa` int(11) unsigned NOT NULL DEFAULT '0'," .
      "\nCHANGE `nb_plages` `nb_plages` int(11) unsigned NOT NULL DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.14");
    $query = "ALTER TABLE `temps_hospi` " .
      "\nCHANGE `type` `type` enum('comp','ambu','seances','ssr','psy') NOT NULL DEFAULT 'ambu';";
    $this->addQuery($query);

    $this->makeRevision("0.15");
    $query = "ALTER TABLE `temps_op`
               ADD `reveil_moy` TIME NOT NULL ,
               ADD `reveil_ecart` TIME NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.16");
    $this->setModuleCategory("reporting", "metier");

    $this->mod_version = "0.17";
  }
}
