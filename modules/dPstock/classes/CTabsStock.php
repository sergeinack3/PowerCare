<?php

/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stock;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsStock extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile('vw_idx_order_manager', TAB_EDIT);
        $this->registerFile('vw_return_forms', TAB_EDIT);
        $this->registerFile('vw_idx_stock_group', TAB_EDIT);
        $this->registerFile('vw_idx_stock_service', TAB_EDIT);
        $this->registerFile('vw_idx_reference', TAB_EDIT);
        $this->registerFile('vw_idx_product', TAB_EDIT);
        $this->registerFile('vw_idx_movements', TAB_READ);
        $this->registerFile('vw_idx_setup', TAB_EDIT);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
