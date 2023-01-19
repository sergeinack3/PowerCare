<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupGenericImport extends CSetup
{
    /**
     * @inheritDoc
     */
    function __construct()
    {
        parent::__construct();

        $this->mod_name = "genericImport";
        $this->makeRevision("0.0");
        $this->setModuleCategory("import", "ox");

        $this->addDependency('import', '0.03');

        $this->makeRevision('0.01');

        $query = "CREATE TABLE `import_file` (
                `import_file_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `import_campaign_id` INT (11) UNSIGNED NOT NULL,
                `file_name` VARCHAR (255) NOT NULL,
                `entity_type` VARCHAR(80),
                INDEX `campaign_type` (`import_campaign_id`, `entity_type`)
              )/*! ENGINE=MyISAM */";
        $this->addQuery($query);

        $this->mod_version = "0.02";
    }
}
