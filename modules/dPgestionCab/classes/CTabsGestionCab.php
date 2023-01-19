<?php
/**
 * @package Mediboard\GestionCab
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\GestionCab;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsGestionCab extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile("edit_compta", TAB_READ);
        $this->registerFile("edit_paie", TAB_READ);
        $this->registerFile("edit_params", TAB_READ);
        $this->registerFile("edit_mode_paiement", TAB_READ);
        $this->registerFile("edit_rubrique", TAB_READ);
    }
}



