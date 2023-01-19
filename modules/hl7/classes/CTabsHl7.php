<?php

/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsHl7 extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile("vw_read_hl7v2_files", TAB_EDIT);
        $this->registerFile("vw_display_hl7v2_message", TAB_READ);
        $this->registerFile("vw_test_hl7v2", TAB_ADMIN);
        $this->registerFile("vw_hl7v2_tables", TAB_EDIT);
        $this->registerFile("vw_hl7v2_schemas", TAB_READ);
        $this->registerFile("vw_movements", TAB_EDIT);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
