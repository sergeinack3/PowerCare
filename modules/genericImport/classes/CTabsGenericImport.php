<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsGenericImport extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile('vw_importable_objects', TAB_READ);
        $this->registerFile('vw_import', TAB_EDIT);
        $this->registerFile('vw_import_sql', TAB_EDIT);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
