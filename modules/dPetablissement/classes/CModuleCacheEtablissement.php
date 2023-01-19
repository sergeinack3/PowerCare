<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Etablissement;

use Ox\Core\Module\AbstractModuleCache;

class CModuleCacheEtablissement extends AbstractModuleCache
{
    protected array $shm_patterns = [
        'CGroups',
    ];

    public function getModuleName(): string
    {
        return 'dPetablissement';
    }
}
