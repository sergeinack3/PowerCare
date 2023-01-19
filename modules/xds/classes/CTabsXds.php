<?php

/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsXds extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile('vw_tools_xds', TAB_ADMIN);
        $this->registerFile('vw_search_documents', TAB_ADMIN);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
