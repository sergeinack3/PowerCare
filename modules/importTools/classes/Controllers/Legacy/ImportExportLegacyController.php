<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\ImportTools\Controllers\Legacy;

use Exception;
use Ox\Core\CLegacyController;
use Ox\Core\CMbString;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;

class ImportExportLegacyController extends CLegacyController
{
    /**
     * @throws Exception
     */
    public function vwTransfertPatients(): void
    {
        $this->checkPermRead();

        $file = file_get_contents(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'transfert_patients.md');
        if ($file) {
            echo "<div>" . CMbString::markdown($file) . "</div>";
        }

        $current_group = CGroups::loadCurrent();

        $this->renderSmarty(
            'vw_transfert_patients',
            [
                'groups' => CGroups::loadGroups(),
                'current_group' => $current_group,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function listFunctionsByGroup(): void
    {
        $this->checkPermRead();
        $group_id = CValue::get("group_id");
        CView::checkin();

        $group = CGroups::get($group_id);

        $this->renderSmarty(
            'inc_vw_transfert_patients_functions',
            [
                'functions' => $group->loadFunctions(),
            ]
        );
    }
}
