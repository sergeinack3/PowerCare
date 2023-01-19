<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsSample extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile('readme', TAB_READ);
        $this->registerFile('displayMovies', TAB_READ);

        $this->registerFile('displayPersons', TAB_ADMIN, self::TAB_SETTINGS);

        $this->registerFile('legacy_compat', TAB_READ);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
