<?php

/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms;

use Ox\Core\Module\AbstractTabsRegister;
use Ox\Mediboard\System\Forms\CExClass;

/**
 * @codeCoverageIgnore
 */
class CTabsForms extends AbstractTabsRegister
{
    /**
     * @inheritDoc
     */
    public function registerAll(): void
    {
        $this->registerFile("view_ex_class", TAB_EDIT);
        $this->registerFile("view_ex_list", TAB_EDIT);
        $this->registerFile("view_ex_concept", TAB_EDIT);
        $this->registerFile("view_ex_class_category", CExClass::inHermeticMode(false) ? TAB_ADMIN : TAB_EDIT);
        $this->registerFile("view_ex_object_explorer", CExClass::inHermeticMode(false) ? TAB_ADMIN : TAB_EDIT);
        $this->registerFile("vw_import_ex_class", TAB_EDIT);
        $this->registerFile("view_stats", CExClass::inHermeticMode(false) ? TAB_ADMIN : TAB_READ);
        $this->registerFile("vwRefChecker", TAB_ADMIN);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
