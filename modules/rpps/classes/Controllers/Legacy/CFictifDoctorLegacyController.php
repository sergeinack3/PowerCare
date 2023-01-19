<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps\Controllers\Legacy;

use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CSpecCPAM;
use Ox\Mediboard\Patients\CMedecin;

/**
 * Description
 */
class CFictifDoctorLegacyController extends CLegacyController
{
    /**
     * @throws \Exception
     *
     * @return void
     */
    public function add_edit_fictif_doctor(): void
    {
        $this->checkPermAdmin();

        $medecin_id = CView::get('doctor_id', 'ref class|CMedecin');

        CView::checkin();

        $medecin = CMedecin::findOrNew($medecin_id);

        $this->renderSmarty(
            'inc_edit_fictif_doctor',
            [
                'object'    => $medecin,
                'spec_cpam' => CSpecCPAM::getList(),
            ]
        );
    }
}
