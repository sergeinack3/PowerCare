<?php

/**
 * @package Mediboard\Provenance
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Provenance;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsProvenance extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile('vw_provenances', TAB_EDIT);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
