<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms;

use Ox\Core\CacheManager;
use Ox\Core\Module\AbstractModuleCache;
use Ox\Mediboard\System\Forms\CExObject;

class CModuleCacheForms extends AbstractModuleCache
{
    protected array $dshm_patterns = [
        CExObject::CACHE_KEY,
    ];

    public function getModuleName(): string
    {
        return 'forms';
    }

    /**
     * @inheritdoc
     */
    public function clearSpecialActions(): void
    {
        CExObject::clearLocales(true);

        CacheManager::output("module-forms-msg-cache-ex_class-suppr", UI_MSG_OK);
    }
}
