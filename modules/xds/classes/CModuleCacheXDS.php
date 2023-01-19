<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds;

use Ox\Core\CacheManager;
use Ox\Core\Module\AbstractModuleCache;
use Ox\Interop\Xds\Factory\CXDSFactory;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class CModuleCacheXDS
 * @package Ox\Interop\Xds
 */
class CModuleCacheXDS extends AbstractModuleCache
{
    public function getModuleName(): string
    {
        return 'xds';
    }

    /**
     * @inheritdoc
     * @throws InvalidArgumentException
     */
    public function clearSpecialActions(): void
    {
        parent::clearSpecialActions();

        $repo_xds = new CXDSRepository(CXDSFactory::TYPE_XDS);
        if ($repo_xds->clearCache()) {
            CacheManager::output('CXDSFactory-msg-success deleted cache');
        }
    }
}
