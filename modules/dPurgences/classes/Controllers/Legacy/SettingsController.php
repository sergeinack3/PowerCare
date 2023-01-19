<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences\Controllers\Legacy;

use Ox\Core\CLegacyController;
use Ox\Mediboard\Hospi\CInfoType;

class SettingsController extends CLegacyController
{
    public function infosServiceTypes(): void
    {
        $this->checkPermAdmin();

        $types = CInfoType::loadForUser();
        array_walk(
            $types,
            function (CInfoType $type): void {
                $type->countInfos();
            }
        );

        $this->renderSmarty('info_service_types', ['types' => $types]);
    }
}
