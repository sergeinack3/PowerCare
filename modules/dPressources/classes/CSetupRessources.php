<?php
/**
 * @package Mediboard\Ressources
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ressources;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupRessources extends CSetup {

  function __construct() {
    parent::__construct();

    $this->mod_name = "dPressources";

    $this->makeRevision("0.0");
    $query = "CREATE TABLE `plageressource` (
                 `plageressource_id` BIGINT NOT NULL AUTO_INCREMENT ,
                 `prat_id` BIGINT,
                 `date` DATE NOT NULL ,
                 `debut` TIME NOT NULL ,
                 `fin` TIME NOT NULL ,
                 `tarif` FLOAT DEFAULT '0' NOT NULL ,
                 `paye` TINYINT DEFAULT '0' NOT NULL ,
                 `libelle` VARCHAR( 50 ) ,
                 PRIMARY KEY ( `plageressource_id` )
               ) /*! ENGINE=MyISAM */ COMMENT = 'Table des plages de ressource';";
    $this->addQuery($query);

    $this->makeRevision("0.1");
    $query = "ALTER TABLE `plageressource` ADD INDEX ( `prat_id` ) ;";
    $this->addQuery($query);

    $this->makeRevision("0.11");
    $query = "ALTER TABLE `plageressource` " .
      "\nCHANGE `plageressource_id` `plageressource_id` int(11) unsigned NOT NULL AUTO_INCREMENT," .
      "\nCHANGE `prat_id` `prat_id` int(11) unsigned NULL," .
      "\nCHANGE `paye` `paye` enum('0','1') NOT NULL DEFAULT '0'," .
      "\nCHANGE `libelle` `libelle` varchar(255) NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.12");
    $query = "UPDATE `plageressource` SET prat_id = NULL WHERE prat_id='0';";
    $this->addQuery($query);

    $this->makeRevision("0.13");
    $query = "ALTER TABLE `plageressource` 
              ADD INDEX (`date`);";
    $this->addQuery($query);

    $this->makeRevision("0.14");
    $this->setModuleCategory("plateau_technique", "metier");

    $this->mod_version = "0.15";
  }
}
