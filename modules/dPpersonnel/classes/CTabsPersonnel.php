<?php
/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Personnel;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsPersonnel extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile("vw_edit_personnel", TAB_READ);
        $this->registerFile("vw_affectations_pers", TAB_READ);
        $this->registerFile("vw_affectations_multiples", TAB_EDIT);
        $this->registerFile("vw_idx_plages_conge", TAB_READ);
        $this->registerFile("vw_planning_conge", TAB_READ);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
