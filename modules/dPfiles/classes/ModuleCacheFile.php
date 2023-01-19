<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Exception;
use Ox\Core\CMbPath;
use Ox\Core\Module\AbstractModuleCache;

class ModuleCacheFile extends AbstractModuleCache
{
    public function getModuleName(): string
    {
        return 'dPfiles';
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function clearSpecialActions(): void
    {
        parent::clearSpecialActions();

        $dir = CFile::getThumbnailDir();

        if (is_dir($dir)) {
            CMbPath::emptyDir($dir);
        }
    }
}

