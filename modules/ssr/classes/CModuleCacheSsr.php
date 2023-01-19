<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\Module\AbstractModuleCache;

class CModuleCacheSsr extends AbstractModuleCache
{
    protected array $shm_patterns = [
        'activite_csarr_',
    ];

    public function getModuleName(): string
    {
        return 'ssr';
    }
}
