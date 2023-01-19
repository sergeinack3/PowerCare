<?php

/**
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Astreintes;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupAstreintes extends CSetup
{
    public function __construct()
    {
        parent::__construct();

        $this->mod_name = "astreintes";
        $this->makeRevision("0.0");

        $query = "CREATE TABLE `plage_astreinte` (
      `plage_id` INT (11) UNSIGNED NOT NULL auto_increment,
      `date_debut` DATE NOT NULL,
      `date_fin` DATE NOT NULL,
      `libelle` VARCHAR (255) NOT NULL,
      `user_id` INT (11) UNSIGNED,
      PRIMARY KEY (`plage_id`)
      ) /*! ENGINE=MyISAM */ COMMENT='table des astreintes'";
        $this->addQuery($query);


        $this->makeRevision("0.1");
        $query = "RENAME TABLE `plage_astreinte` TO `astreinte_plage`";
        $this->addQuery($query);

        $query = "ALTER TABLE `astreinte_plage`
      ADD `type` VARCHAR (255) NOT NULL,
      CHANGE `date_debut` `start` DATETIME NOT NULL,
      CHANGE `date_fin` `end` DATETIME NOT NULL,
      ADD `phone_astreinte` INT (11) UNSIGNED";
        $this->addQuery($query);


        $this->makeRevision("0.2");
        $query = "ALTER TABLE `astreinte_plage`
      CHANGE `libelle` `libelle` VARCHAR (255),
      CHANGE `user_id` `user_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
      CHANGE `type` `type` ENUM ('medical','admin','personnelsoignant') NOT NULL,
      CHANGE `phone_astreinte` `phone_astreinte` VARCHAR (20) NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.3");
        $query = "ALTER TABLE `astreinte_plage`
                ADD INDEX (`user_id`),
                ADD INDEX (`start`),
                ADD INDEX (`end`);";
        $this->addQuery($query);

        $this->makeRevision("0.4");
        $query = "ALTER TABLE `astreinte_plage`
      CHANGE `phone_astreinte` `phone_astreinte` VARCHAR (30) NOT NULL,
      ADD `group_id` INT (11) UNSIGNED NOT NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `astreinte_plage`
      ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.5");
        $query = "ALTER TABLE `astreinte_plage`
      CHANGE `type` `type` ENUM ('admin','informatique','medical','personnelsoignant','technique') NOT NULL,
      ADD `choose_astreinte` ENUM ('ponctuelle','reguliere') NOT NULL DEFAULT 'ponctuelle',
      ADD `color` VARCHAR (6),
      ADD `date` DATE NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.6");

        $this->addDefaultConfig("astreintes General astreinte_admin_color", "astreintes astreinte_admin_color");
        $this->addDefaultConfig(
            "astreintes General astreinte_informatique_color",
            "astreintes astreinte_informatique_color"
        );
        $this->addDefaultConfig("astreintes General astreinte_medical_color", "astreintes astreinte_medical_color");
        $this->addDefaultConfig(
            "astreintes General astreinte_personnelsoignant_color",
            "astreintes astreinte_personnelsoignant_color"
        );
        $this->addDefaultConfig("astreintes General astreinte_technique_color", "astreintes astreinte_technique_color");

        $this->makeRevision("0.61");

        $query = "ALTER TABLE `astreinte_plage`
                ADD `categorie` INT (11) UNSIGNED,
                ADD `locked` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "CREATE TABLE `oncall_category` (
                `oncall_category_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name` VARCHAR (255) NOT NULL,
                `color` VARCHAR (6)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.7");

        $this->addPrefQuery("categorie-astreintes", "");

        $this->makeRevision("0.8");
        $this->setModuleCategory("administratif", "metier");

        $this->makeRevision("0.9");

        $query = "ALTER TABLE `oncall_category`
	            ADD `group_id` INT (11) NULL;";
        $this->addQuery($query);

        $this->makeRevision("1.0");

        $query = "ALTER TABLE `astreinte_plage`
	            ADD INDEX (`categorie`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `oncall_category`
	            ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $this->mod_version = "1.1";
    }
}
