<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sante400;

namespace Ox\Mediboard\Sante400;

use Ox\Core\Module\CModule;
use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupSante400 extends CSetup {

  /**
   * Standard constructor
   */
  function __construct() {
    parent::__construct();

    $this->mod_name = "dPsante400";

    $this->makeRevision("0.0");
    $query = "CREATE TABLE `id_sante400` (
      `id_sante400_id` INT NOT NULL AUTO_INCREMENT ,
      `object_class` VARCHAR( 25 ) NOT NULL ,
      `object_id` INT NOT NULL ,
       `tag` VARCHAR( 80 ) ,
       `last_update` DATETIME NOT NULL ,
        PRIMARY KEY ( `id_sante400_id` ) ,
        INDEX ( `object_class` , `object_id` , `tag` )) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.1");
    $query = "ALTER TABLE `id_sante400` ADD `id400` VARCHAR( 8 ) NOT NULL ;";
    $this->addQuery($query);

    $this->makeRevision("0.11");
    $query = "ALTER TABLE `id_sante400` CHANGE `id400` `id400` VARCHAR( 10 ) NOT NULL ;";
    $this->addQuery($query);

    $this->makeRevision("0.12");
    $query = "ALTER TABLE `id_sante400`
      CHANGE `id_sante400_id` `id_sante400_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      CHANGE `object_id` `object_id` int(11) unsigned NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.13");
    $query = "ALTER TABLE `id_sante400` DROP INDEX `object_class` ;";
    $this->addQuery($query);
    $query = "ALTER TABLE `id_sante400` ADD INDEX ( `object_class` ) ;";
    $this->addQuery($query);
    $query = "ALTER TABLE `id_sante400` ADD INDEX ( `object_id` ) ;";
    $this->addQuery($query);
    $query = "ALTER TABLE `id_sante400` ADD INDEX ( `tag` ) ;";
    $this->addQuery($query);
    $query = "ALTER TABLE `id_sante400` ADD INDEX ( `last_update` ) ;";
    $this->addQuery($query);
    $query = "ALTER TABLE `id_sante400` ADD INDEX ( `id400` ) ;";
    $this->addQuery($query);

    $this->makeRevision("0.14");
    $query = "UPDATE `id_sante400` 
      SET `tag`='labo code4' 
      WHERE `tag`='LABO' 
      AND `object_class`='CMediusers' ;";
    $this->addQuery($query);

    $this->makeRevision("0.15");
    $query = "UPDATE `id_sante400`
      SET `tag` = CONCAT('NDOS ', LEFT(`tag`, 8))
      WHERE `object_class` = 'CSejour'
      AND `tag` LIKE 'CIDC:___ DMED:________'";
    $this->addQuery($query);

    $this->makeRevision("0.16");
    $query = "CREATE TABLE `trigger_mark` (
      `mark_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `trigger_class` VARCHAR (255) NOT NULL,
      `trigger_number` VARCHAR (10) NOT NULL,
      `mark` VARCHAR (255) NOT NULL,
      `done` ENUM ('0','1') NOT NULL
    ) /*! ENGINE=MyISAM */;";

    $this->addQuery($query);

    $this->makeRevision("0.17");
    $query = "ALTER TABLE `trigger_mark` ADD UNIQUE `trigger_unique` (
      `trigger_class` ,
      `trigger_number`
    );";
    $this->addQuery($query);
    $query = "ALTER TABLE `trigger_mark` ADD INDEX ( `mark` );";
    $this->addQuery($query);

    $this->makeRevision("0.18");
    $query = "ALTER TABLE `trigger_mark` 
       CHANGE `trigger_number` `trigger_number` BIGINT (10) UNSIGNED ZEROFILL NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.19");
    $query = "ALTER TABLE `id_sante400` 
       CHANGE `id400` `id400` VARCHAR  (25) NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.20");
    $query = "ALTER TABLE `id_sante400` 
       CHANGE `id400` `id400` VARCHAR  (80) NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.21");
    $query = "ALTER TABLE `id_sante400` 
       CHANGE `object_class` `object_class` VARCHAR (40) NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.22");
    $query = "CREATE TABLE `incrementer` (
      `incrementer_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `type` ENUM ('IPP','NDA') NOT NULL,
      `group_id` INT (11) UNSIGNED NOT NULL,
      `last_update` DATETIME NOT NULL,
      `value` VARCHAR (255) NOT NULL DEFAULT '1',
      `pattern` VARCHAR (255) NOT NULL
     ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = "ALTER TABLE `incrementer` 
      ADD INDEX (`last_update`);";
    $this->addQuery($query);

    $this->makeRevision("0.23");
    $query = "ALTER TABLE `incrementer` 
      CHANGE `type` `object_class` ENUM ('CPatient','CSejour') NOT NULL;";
    $this->addQuery($query);
    $query = "ALTER TABLE `incrementer` 
      ADD INDEX (`object_class`),
      ADD INDEX (`group_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.24");
    $query = "ALTER TABLE `trigger_mark` 
      ADD INDEX ( `trigger_class` ),
      ADD INDEX ( `trigger_number` );";
    $this->addQuery($query);

    $this->makeRevision("0.25");
    $query = "ALTER TABLE `incrementer` 
      ADD `range_min` INT (11) UNSIGNED,
      ADD `range_max` INT (11);";
    $this->addQuery($query);

    $this->makeRevision("0.26");
    $query = "ALTER TABLE `trigger_mark`
      ADD `when` DATETIME,
     CHANGE `done` `done` ENUM ('0','1') NOT NULL DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision('0.27');

    $query = "CREATE TABLE `hypertext_link` (
                `hypertext_link_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name` VARCHAR (255) NOT NULL,
                `link` VARCHAR (255) NOT NULL,
                `object_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `object_class` VARCHAR (80) NOT NULL
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $query = 'ALTER TABLE `hypertext_link`
                ADD INDEX (`object_id`),
                ADD INDEX (`object_class`);';
    $this->addQuery($query);

    $this->makeRevision("0.28");

    if (CModule::getInstalled("hprim21")) {
      $this->addDefaultConfig("dPsante400 CIdSante400 admit_ipp_nda_obligatory", "hprim21 mandatory_num_dos_ipp_adm");
    }

    $this->makeRevision("0.29");
    $query = "ALTER TABLE `id_sante400`
                ADD `datetime_create` DATETIME NOT NULL,
                ADD INDEX ( `datetime_create` ) ;";
    $this->addQuery($query);

    $query = "UPDATE `id_sante400`
      SET `datetime_create` = `last_update`";
    $this->addQuery($query);

    $this->makeRevision("0.30");
    $query = "ALTER TABLE `id_sante400`
                MODIFY `object_class` CHAR (40) NOT NULL,
                MODIFY `tag`          CHAR (80),
                MODIFY `id400`        CHAR (80) NOT NULL;";
    $this->addQuery($query);

    $this->makeRevision("0.31");
    $query = "ALTER TABLE `incrementer`
                ADD `extra_data` VARCHAR (10),
                ADD `reset_value` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $this->makeRevision("0.32");
    $query = "ALTER TABLE `incrementer`
                ADD `manage_range` ENUM ('0','1') NOT NULL DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.33");
    $this->setModuleCategory("systeme", "administration");

    $this->makeRevision('0.34');

    $this->addQuery(
        'ALTER TABLE `id_sante400`
            ADD INDEX `target` (`object_class`, `object_id`)'
    );

    $this->mod_version = '0.35';
  }
}
