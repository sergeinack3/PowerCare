<?php

/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsEai extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile("vw_idx_interop_actors", TAB_READ);
        $this->registerFile("vw_idx_exchanges", TAB_READ);
        $this->registerFile("vw_sources", TAB_READ);
        $this->registerFile("vw_routers", TAB_ADMIN);
        $this->registerFile("vw_transformations", TAB_ADMIN);
        $this->registerFile("vw_servers_socket", TAB_ADMIN);
        $this->registerFile("vw_domains", TAB_ADMIN);
        $this->registerFile('vw_tunnel_tools', TAB_ADMIN);
        $this->registerFile("vw_stats", TAB_ADMIN);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
