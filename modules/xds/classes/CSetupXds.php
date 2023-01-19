<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupXds extends CSetup {
  /**
   * @see parent::__construct()
   */
  function __construct() {
    parent::__construct();
    
    $this->mod_name = "xds";

    $this->makeRevision("0.0");

    $this->makeRevision("0.01");
    $this->addDependency("cda", "0.01");
    $query = "CREATE TABLE `cxds_submissionlot` (
    `cxds_submissionlot_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `title` VARCHAR (255),
                `comments` VARCHAR (255),
                `date` DATETIME,
                `type` VARCHAR (255) NOT NULL
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.02");

    $query = "ALTER TABLE `cxds_submissionlot`
                ADD INDEX (`date`);";
    $this->addQuery($query);

    $this->makeRevision("0.03");

    $query = "CREATE TABLE `cxds_submissionlot_document` (
                `cxds_submissionlot_document_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `submissionlot_id` INT (11) UNSIGNED,
                `object_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `object_class` ENUM ('CCompteRendu','CFile') NOT NULL
              )/*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.04");

    $query = "ALTER TABLE `cxds_submissionlot_document`
                ADD INDEX (`submissionlot_id`),
                ADD INDEX (`object_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.05");

    $query = "CREATE TABLE `xds_document` (
                `xds_document_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `object_class` VARCHAR (80) NOT NULL,
                `object_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `version` INT (11),
                `date` DATETIME,
                `etat` VARCHAR (255),
                `visibilite` ENUM ('0','1'),
                `patient_id` INT (11) UNSIGNED
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `xds_document`
                ADD INDEX (`object_class`),
                ADD INDEX (`object_id`),
                ADD INDEX (`date`),
                ADD INDEX (`patient_id`);";
    $this->addQuery($query);

    $this->makeRevision("0.06");

    $query = "CREATE TABLE `xds_file` (
                `xds_file_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `file_id` INT (11) UNSIGNED,
                `author_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `patient_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `version` VARCHAR (255) NOT NULL,
                `create_date` DATETIME NOT NULL,
                `legal_author` VARCHAR (255),
                `author` VARCHAR (255),
                `profession` VARCHAR (255),
                `title` VARCHAR (255) NOT NULL,
                `description` TEXT,
                `event_date_start` DATETIME,
                `event_date_end` DATETIME,
                `type_document` VARCHAR (255),
                `category_xds` VARCHAR (255),
                `confidentiality` VARCHAR (255),
                `language` VARCHAR (255)
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `xds_file`
                ADD INDEX (`file_id`),
                ADD INDEX (`author_id`),
                ADD INDEX (`patient_id`),
                ADD INDEX (`create_date`),
                ADD INDEX (`event_date_start`),
                ADD INDEX (`event_date_end`);";
    $this->addQuery($query);

    $this->makeRevision("0.07");
    $this->setModuleCategory("interoperabilite", "echange");

    $this->makeRevision("0.08");

    $this->moveConfiguration('dmp general information_certificat', 'xds general use_siret_finess_ans');

    $this->mod_version = "0.09";
  }
}
