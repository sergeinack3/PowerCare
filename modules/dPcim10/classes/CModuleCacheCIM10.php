<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10;

use Ox\Core\Module\AbstractModuleCache;

class CModuleCacheCIM10 extends AbstractModuleCache
{
    protected array $shm_patterns = [
        'CCodeCIM10OMS',
        'CCodeCIM10ATIH',
        'CDRCConsultationResult',
        'CCIM10CategoryATIH',
        'CCategoryCIM10GM',
        'CCodeCIM10GM',
        'CCISP',
    ];

    public function getModuleName(): string
    {
        return 'dPcim10';
    }
}
