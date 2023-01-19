<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Controllers\Legacy;

use Exception;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

/**
 * Handicap Patient Controller
 */
class PatientHandicapController extends CLegacyController
{
    /**
     * Check disability for a patient
     *
     * @return void
     * @throws Exception
     */
    public function checkDisability(): void
    {
        $this->checkPermEdit();

        $patient_id = CView::get('patient_id', 'ref class|CPatient');

        CView::checkin();

        $handicap = [];
        $patient  = CPatient::find($patient_id);
        $patient->loadRefsPatientHandicaps();

        foreach ($patient->_refs_patient_handicaps as $_patient_handicap) {
            $handicap[] = [
                "handicap" => $_patient_handicap->handicap,
            ];
        }

        $this->renderJson($handicap);
    }
}
