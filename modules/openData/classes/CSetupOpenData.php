<?php

/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\OpenData;

use Ox\Core\CAppUI;
use Ox\Core\CSetup;
use Ox\Core\CSQLDataSource;

/**
 * @codeCoverageIgnore
 */
class CSetupOpenData extends CSetup
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->mod_name = "openData";
        $this->makeRevision("0.0");

        $this->addDependency("dPpatients", "3.29");
        $this->addMethod('checkTablesINSEE');

        $this->makeRevision('0.0.1');

        $ds_insee = CSQLDataSource::get('INSEE', true);
        $ds       = CSQLDataSource::get('std');

        if ($ds_insee && !$ds_insee->hasTable('communes_france_new')) {
            $query = "CREATE TABLE `communes_france_new` (
              `commune_france_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `INSEE` VARCHAR (5) NOT NULL,
              `commune` VARCHAR (80) NOT NULL,
              `departement` VARCHAR (80) NOT NULL,
              `region` VARCHAR (80) NOT NULL,
              `statut` ENUM ('comm','cheflieu','souspref','pref','prefregion','capital') DEFAULT 'comm',
              `superficie` INT (11) UNSIGNED,
              `population` INT (11) UNSIGNED,
              `point_geographique` VARCHAR (255),
              `forme_geographique` TEXT
            )/*! ENGINE=MyISAM */;";
            $this->addQuery($query, false, 'INSEE');

            $query = "ALTER TABLE `communes_france_new`
              ADD INDEX (`commune`),
              ADD INDEX (`departement`),
              ADD INDEX (`region`);";
            $this->addQuery($query, false, 'INSEE');
        }

        if ($ds_insee && !$ds_insee->hasTable('communes_cp')) {
            $query = "CREATE TABLE `communes_cp` (
              `commune_cp_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `code_postal` VARCHAR (5) NOT NULL,
              `commune_id` INT (11) UNSIGNED NOT NULL
            )/*! ENGINE=MyISAM */;";
            $this->addQuery($query, false, 'INSEE');

            $query = "ALTER TABLE `communes_cp`
              ADD INDEX (`code_postal`), 
              ADD INDEX (`commune_id`);";
            $this->addQuery($query, false, 'INSEE');
        }

        $this->makeRevision('0.01');

        if ($ds_insee && $ds_insee->hasTable('communes_france_new')) {
            $query = "ALTER TABLE `communes_france_new`
            MODIFY `departement` VARCHAR(80),
            MODIFY `region` VARCHAR(80);";
            $this->addQuery($query, false, 'INSEE');
        }

        $this->addDependency("dPpatients", "3.47");

        if (!$ds->hasField('import_conflict', 'ignore')) {
            $this->makeRevision('0.02');

            $query = "ALTER TABLE `import_conflict` 
          ADD `ignore` ENUM ('0','1') DEFAULT '0'";
            $this->addQuery($query);
        } else {
            $this->makeEmptyRevision('0.02');
        }

        if ($ds_insee && !$ds_insee->hasTable('communes_demographie')) {
            $this->makeRevision('0.06');
            $query = "CREATE TABLE `communes_demographie` (
                `communes_demographie_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `commune_id` INT (11) UNSIGNED NOT NULL,
                `annee` INT (11) NOT NULL,
                `age_min` INT (11),
                `age_max` INT (11),
                `sexe` ENUM ('m','f'),
                `population` INT (11),
                `nationalite` ENUM ('francais','etranger')
              )/*! ENGINE=MyISAM */;";
            $this->addQuery($query, false, 'INSEE');

            $query = "ALTER TABLE `communes_demographie` 
                ADD INDEX (`commune_id`),
                ADD INDEX (`age_min`),
                ADD INDEX (`age_max`),
                ADD INDEX (`sexe`),
                ADD INDEX (`nationalite`),
                ADD INDEX (`annee`);";
            $this->addQuery($query, false, 'INSEE');
        } else {
            $this->makeEmptyRevision('0.06');
        }

        $this->makeRevision("0.07");
        $this->setModuleCategory("referentiel", "referentiel");

        $this->mod_version = "0.08";

        $query = "SHOW TABLES LIKE 'hd_etablissement'";
        $this->addDatasource("hospi_diag", $query);

        $query = "SHOW TABLES";
        $this->addDatasource('INSEE', $query);
    }

    protected function checkTablesINSEE(): bool
    {
        $ds_insee = CSQLDataSource::get('INSEE', true);

        if (!$ds_insee || !$ds_insee instanceof CSQLDataSource) {
            CAppUI::setMsg("DSN INSEE MISSING", UI_MSG_ERROR);

            return false;
        }

        return true;
    }
}
