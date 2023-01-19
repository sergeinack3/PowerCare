<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CacheManager;
use Ox\Core\Module\AbstractModuleCache;
use Ox\Core\Module\CModule;

class CModuleCacheSystem extends AbstractModuleCache
{
    protected array $shm_patterns = [
        'CApp',
        'CClassMap',
        "class-paths",
        "CObjectClass.getID",
    ];

    protected array $dshm_patterns = [
        "index-",
        'CCronJobLog',
    ];

    public function getModuleName(): string
    {
        return 'system';
    }

    /**
     * @inheritdoc
     */
    public function clearSpecialActions(): void
    {
        parent::clearSpecialActions();
        CacheManager::output('CModuleAction-msg-%d deleted cached ID|pl', UI_MSG_OK, CModuleAction::clearCacheIDs());
        CModule::clearCacheRequirements();
    }
}
