<?php

/**
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Reservation;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsReservation extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile('vw_sejours_validation', TAB_READ);
        $this->registerFile("vw_planning", TAB_READ);
        $this->registerFile("vw_edit_sejour", TAB_READ);
        $this->registerFile("vw_suivi_salles", TAB_EDIT);
        $this->registerFile("vw_print_planning", TAB_EDIT);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
