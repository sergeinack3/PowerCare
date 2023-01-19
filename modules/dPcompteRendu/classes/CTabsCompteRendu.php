<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsCompteRendu extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile('vw_modeles', TAB_READ);
        $this->registerFile('vw_idx_aides', TAB_READ);
        $this->registerFile('vw_idx_listes', TAB_READ);
        $this->registerFile('vw_idx_packs', TAB_READ);
        $this->registerFile('vw_stats', TAB_ADMIN);

        $this->registerFile('vw_whitelist', TAB_ADMIN, self::TAB_SETTINGS);

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
