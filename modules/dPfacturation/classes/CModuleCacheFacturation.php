<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Ox\Core\Module\AbstractModuleCache;

class CModuleCacheFacturation extends AbstractModuleCache
{
    protected array $dshm_patterns = [
        'CFacture',
    ];

    public function getModuleName(): string
    {
        return 'dPfacturation';
    }
}
