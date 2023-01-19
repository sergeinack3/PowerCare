<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use Ox\Core\Module\AbstractModuleCache;

class CModuleCacheCompteRendu extends AbstractModuleCache
{
    protected array $dshm_patterns = [
        CCompteRendu::CACHE_KEY_OPENER,
    ];

    public function getModuleName(): string
    {
        return 'dPcompteRendu';
    }
}
