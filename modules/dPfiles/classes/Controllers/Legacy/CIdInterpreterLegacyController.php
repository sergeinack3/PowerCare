<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Controllers\Legacy;

use Ox\Core\CLegacyController;
use Ox\Core\CView;

/**
 * Description
 */
class CIdInterpreterLegacyController extends CLegacyController
{
    public function idInterpreter(): void
    {
        $this->checkPermEdit();

        $patient_id = CView::get('patient_id', 'ref class|CPatient');
        CView::checkin();

        $this->renderSmarty('id_interpreter', ['patient_id' => $patient_id]);
    }
}
