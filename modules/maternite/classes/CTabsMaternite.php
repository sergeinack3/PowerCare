<?php

/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsMaternite extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile("vw_placement_patients", TAB_READ);
        $this->registerFile("vw_tdb_maternite", TAB_READ);
        $this->registerFile("vw_tdb_naissances", TAB_READ);
        $this->registerFile("vw_admissions", TAB_READ);
        $this->registerFile("vw_grossesses", TAB_READ);
        $this->registerFile("vw_placement", TAB_READ);
        $this->registerFile("vw_consultations", TAB_READ);
        $this->registerFile("vw_registre", TAB_READ);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
