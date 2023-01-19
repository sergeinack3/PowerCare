<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences\Controllers\Legacy;

use Ox\Core\CLegacyController;
use Ox\Mediboard\Urgences\ExportRPU;

class ConfigureController extends CLegacyController
{
    public function configure(): void
    {
        $this->checkPermAdmin();

        $this->renderSmarty(
            'configure',
            [
                'export_source' => ExportRPU::getSource(),
            ]
        );
    }
}
