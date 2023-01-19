<?php

/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sante400;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsSante400 extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile("view_identifiants", TAB_READ);
        $this->registerFile("stats_identifiants", TAB_READ);
        $this->registerFile("synchro_sante400", TAB_EDIT);
        $this->registerFile("view_marks", TAB_READ);
        $this->registerFile("delete_duplicates", TAB_ADMIN);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
