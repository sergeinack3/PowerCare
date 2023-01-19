<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Ox\Core\Module\AbstractModuleCache;

class CModuleCacheCCAM extends AbstractModuleCache
{
    protected array $shm_patterns = [
        'CCodeCCAM',
        'COldCodeCCAM',
        'CDatedCodeCCAM',
        'CDentCCAM',
        'CCodeNGAP',
        'CCCAM',
        'CActiviteModificateurCCAM',
        'CActiviteCCAM',
        'CActiviteClassifCCAM',
        'CInfoTarifCCAM',
    ];

    public function getModuleName(): string
    {
        return 'dPccam';
    }
}
