<?php
/**
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Repas;

use Ox\Core\CMbDT;
use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupRepas extends CSetup {
  /**
   * Menu upgrade
   *
   * @return bool
   * @throws \Exception
   */
  protected function fixRepet() {
    $ds = $this->ds;

    $query = "SELECT * FROM menu";
    $menus = $ds->loadList($query);
    foreach ($menus as $menu) {
      $nbDays  = CMbDT::daysRelative($menu["debut"], $menu["fin"]);
      $nbWeeks = floor($nbDays / 7);
      if (!$nbWeeks) {
        $menu["nb_repet"] = 1;
      }
      else {
        $menu["nb_repet"] = ceil($nbWeeks / $menu["repetition"]);
      }
      $query = "UPDATE `menu` SET `nb_repet` = '" . $menu["nb_repet"] . "' WHERE(`menu_id`='" . $menu["menu_id"] . "');";
      $ds->exec($query);
      $ds->error();
      $query = "UPDATE `repas` SET `typerepas_id`='" . $menu["typerepas"] . "' WHERE(`menu_id`='" . $menu["menu_id"] . "');";
      $ds->exec($query);
      $ds->error();
    }
    $query = "ALTER TABLE `menu` DROP `fin`;";
    $ds->exec($query);
    $ds->error();

    return true;
  }

  function __construct() {
    parent::__construct();

    $this->mod_name = "dPrepas";

    $this->makeRevision("0.0");
    $query = "CREATE TABLE `menu` (
               `menu_id` int(11) unsigned NOT NULL AUTO_INCREMENT ,
               `group_id` int(11) UNSIGNED NOT NULL,
               `nom` VARCHAR( 255 ) NOT NULL ,
               `typerepas` int(11) UNSIGNED NOT NULL,
               `plat1` VARCHAR( 255 ),
               `plat2` VARCHAR( 255 ),
               `plat3` VARCHAR( 255 ),
               `plat4` VARCHAR( 255 ),
               `plat5` VARCHAR( 255 ),
               `boisson` VARCHAR( 255 ),
               `pain` VARCHAR( 255 ),
               `diabete` enum('0','1') NOT NULL DEFAULT '0',
               `sans_sel` enum('0','1') NOT NULL DEFAULT '0',
               `sans_residu` enum('0','1') NOT NULL DEFAULT '0',
               `modif` enum('0','1') NOT NULL DEFAULT '1',
               `debut` date NOT NULL,
               `fin` date NOT NULL,
               `repetition` int(11) unsigned NOT NULL,
               PRIMARY KEY ( `menu_id` )) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "CREATE TABLE `plats` (
               `plat_id` int(11) unsigned NOT NULL AUTO_INCREMENT ,
               `group_id` int(11) UNSIGNED NOT NULL,
               `nom` VARCHAR( 255 ) NOT NULL ,
               `type` enum('plat1','plat2','plat3','plat4','plat5','boisson','pain') NOT NULL DEFAULT 'plat1',
               `typerepas` int(11) UNSIGNED NOT NULL,
               PRIMARY KEY ( `plat_id` )) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "CREATE TABLE `repas_type` (
               `typerepas_id` int(11) unsigned NOT NULL AUTO_INCREMENT ,
               `group_id` int(11) UNSIGNED NOT NULL,
               `nom` VARCHAR( 255 ) NOT NULL ,
               `debut` time NOT NULL,
               `fin` time NOT NULL,
               PRIMARY KEY ( `typerepas_id` )) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    $query = "CREATE TABLE `repas` (
               `repas_id` int(11) unsigned NOT NULL AUTO_INCREMENT ,
               `affectation_id` int(11) UNSIGNED NOT NULL,
               `menu_id` int(11) UNSIGNED NOT NULL,
               `plat1` int(11) UNSIGNED NULL,
               `plat2` int(11) UNSIGNED NULL,
               `plat3` int(11) UNSIGNED NULL,
               `plat4` int(11) UNSIGNED NULL,
               `plat5` int(11) UNSIGNED NULL,
               `boisson` int(11) UNSIGNED NULL,
               `pain` int(11) UNSIGNED NULL,
               `date` date NOT NULL,
               PRIMARY KEY ( `repas_id` )) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.1");
    $query = "ALTER TABLE `repas` CHANGE `menu_id` `menu_id` int(11) UNSIGNED NULL;";
    $this->addQuery($query);
    $query = "ALTER TABLE `repas` ADD `typerepas_id` int(11) UNSIGNED NOT NULL;";
    $this->addQuery($query);
    $query = "ALTER TABLE `menu` ADD `nb_repet` int(11) unsigned NOT NULL;";
    $this->addQuery($query);
    $this->addMethod("fixRepet");

    $this->makeRevision("0.11");
    $query = "CREATE TABLE `validationrepas` (
               `validationrepas_id` int(11) unsigned NOT NULL AUTO_INCREMENT ,
               `service_id` int(11) UNSIGNED NOT NULL,
               `date` date NOT NULL,
               `typerepas_id` int(11) UNSIGNED NOT NULL,
               PRIMARY KEY ( `validationrepas_id` )) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.12");
    $query = "ALTER TABLE `repas` ADD `modif` enum('0','1') NOT NULL DEFAULT '0';";
    $this->addQuery($query);
    $query = "ALTER TABLE `validationrepas` ADD `modif` enum('0','1') NOT NULL DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.13");
    $query = "ALTER TABLE `menu` 
                ADD INDEX (`group_id`),
                ADD INDEX (`typerepas`),
                ADD INDEX (`debut`);";
    $this->addQuery($query);
    $query = "ALTER TABLE `plats` 
                ADD INDEX (`group_id`),
                ADD INDEX (`typerepas`);";
    $this->addQuery($query);
    $query = "ALTER TABLE `repas` 
                ADD INDEX (`affectation_id`),
                ADD INDEX (`menu_id`),
                ADD INDEX (`plat1`),
                ADD INDEX (`plat2`),
                ADD INDEX (`plat3`),
                ADD INDEX (`plat4`),
                ADD INDEX (`plat5`),
                ADD INDEX (`boisson`),
                ADD INDEX (`pain`),
                ADD INDEX (`date`),
                ADD INDEX (`typerepas_id`);";
    $this->addQuery($query);
    $query = "ALTER TABLE `repas_type` 
                ADD INDEX (`group_id`);";
    $this->addQuery($query);
    $query = "ALTER TABLE `validationrepas` 
                ADD INDEX (`service_id`),
                ADD INDEX (`date`),
                ADD INDEX (`typerepas_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.14");
    $this->setModuleCategory("circuit_patient", "metier");

    $this->mod_version = "0.15";
  }
}
