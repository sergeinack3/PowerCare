<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers\Legacy;

use Exception;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Core\Database\SetupUpdater;

/**
 * Class SetupController
 */
class SetupController extends CLegacyController
{
    /**
     * @throws Exception
     */
    public function updateAllModules(): void
    {
        $this->checkPermAdmin();

        CView::checkin();

        $updater = new SetupUpdater();
        $updater->run();

        /* @todo: What should be displayed then ? */
    }
}
