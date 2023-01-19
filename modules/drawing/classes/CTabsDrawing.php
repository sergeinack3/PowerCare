<?php

/**
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Drawing;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsDrawing extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile("vw_categories", TAB_ADMIN);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
