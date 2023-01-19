<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsCda extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile("vw_datatype", TAB_READ);
        $this->registerFile("vw_highlightCDA", TAB_READ);
        $this->registerFile("vw_showCDA", TAB_READ);
        $this->registerFile("vw_testdatatype", TAB_ADMIN);
        $this->registerFile("vw_toolsdatatype", TAB_ADMIN);
        $this->registerFile("vw_base64", TAB_ADMIN);
        $this->registerFile("vw_parameters", TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
