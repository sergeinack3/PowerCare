<?php

/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\ImportTools;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsImportTools extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile('vw_database_explorer', TAB_ADMIN);
        $this->registerFile('vwTransfertPatients', TAB_ADMIN);
        $this->registerFile('vw_import_tools', TAB_ADMIN);
        $this->registerFile('vw_regression_test', TAB_ADMIN);
        $this->registerFile('vw_import_cron_logs', TAB_ADMIN);
        $this->registerFile('charset_toolbox', TAB_ADMIN);
        $this->registerFile('vw_migration_tools', TAB_READ);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
