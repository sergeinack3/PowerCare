<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsFhir extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile("vw_test_fhir", TAB_EDIT);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
