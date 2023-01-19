<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use Exception;
use Ox\Core\CacheManager;
use Ox\Core\Module\AbstractModuleCache;
use Ox\Mediboard\PlanningOp\CSejour;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class CModuleCacheCDA
 * @package Ox\Interop\Cda
 */
class CModuleCacheCDA extends AbstractModuleCache
{
    public function getModuleName(): string
    {
        return 'cda';
    }

    /**
     * @inheritdoc
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function clearSpecialActions(): void
    {
        parent::clearSpecialActions();

        $repo_cda = new CCDARepository(null, new CSejour());
        if ($repo_cda->clearCache()) {
            CacheManager::output('CCDAFactory-msg-success deleted cache');
        }
    }
}
