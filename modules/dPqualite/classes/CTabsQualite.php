<?php
/**
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Qualite;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsQualite extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile("vw_incident", TAB_READ);
        $this->registerFile("vw_incidentvalid", TAB_READ);
        $this->registerFile("vw_edit_ei", TAB_ADMIN);
        $this->registerFile("vw_stats", TAB_ADMIN);
        $this->registerFile("vw_procedures", TAB_READ);
        $this->registerFile("vw_procencours", TAB_EDIT);
        $this->registerFile("vw_procvalid", TAB_ADMIN);
        $this->registerFile("vw_edit_classification", TAB_ADMIN);
        $this->registerFile("vw_modeles", TAB_EDIT);

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
