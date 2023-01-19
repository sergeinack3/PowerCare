<?php
/**
 * @package Mediboard\Ressources
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ressources;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsRessources extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile("view_planning", TAB_READ);
        $this->registerFile("edit_planning", TAB_EDIT);
        $this->registerFile("view_compta", TAB_EDIT);
    }
}
