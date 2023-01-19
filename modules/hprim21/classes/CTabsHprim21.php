<?php

/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsHprim21 extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile("vw_patients", TAB_READ);
        $this->registerFile("pat_hprim_selector", TAB_READ);
        $this->registerFile("vw_hprim_files", TAB_READ);
        $this->registerFile("vw_display_hprim_message", TAB_READ);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
