<?php
/**
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Repas;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsRepas extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile("vw_edit_menu", TAB_EDIT);
        $this->registerFile("vw_planning_repas", TAB_READ);
        $this->registerFile("vw_edit_repas", TAB_EDIT);
        $this->registerFile("vw_quantite", TAB_EDIT);
        $this->registerFile("vw_create_archive", TAB_ADMIN);
    }
}
