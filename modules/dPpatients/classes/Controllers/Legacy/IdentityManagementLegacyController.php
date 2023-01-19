<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CMbString;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientState;
use Ox\Mediboard\Patients\PatientIdentityService;
use Ox\Mediboard\Patients\Services\PatientQualifierService;

class IdentityManagementLegacyController extends CLegacyController
{
    /**
     * @throws Exception
     */
    public function listPatientState(): void
    {
        if (!CAppUI::pref("allowed_modify_identity_status")) {
            CAppUI::accessDenied();
        }
        $state    = CMbString::upper(CView::get("state", "str notNull default|prov"));
        $page     = (int)CView::get("page", "num notNull default|0");
        $date_min = CView::get("patient_state_date_min", "str", true);
        $date_max = CView::get("patient_state_date_max", "str", true);

        CView::checkin();

        $identity_service = new PatientIdentityService();
        try {
            [$patients, $patients_count] = $identity_service->listPatientsFromState(
                $state,
                $date_min,
                $date_max,
                $page
            );
        } catch (CMbException $e) {
            $e->stepAjax(UI_MSG_ERROR);
        }

        $this->renderSmarty(
            "patient_state/inc_list_patient_state",
            [
                "patients_count" => $patients_count,
                "count"          => $patients_count[$state],
                "patients"       => $patients,
                "state"          => $state,
                "page"           => $page,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function vwMassiveQualify(): void
    {
        if (!CAppUI::pref("allowed_modify_identity_status")) {
            CAppUI::accessDenied();
        }

        $date_min = CView::get('date_min', 'dateTime');
        $date_max = CView::get('date_max', 'dateTime');

        CView::checkin();

        $identity_service = new PatientIdentityService();
        $identity_service->setLimit(1000);

        $patients = [];

        try {
            [$patients, $patients_count] = $identity_service->listPatientsFromState(
                CPatientState::STATE_VALI,
                $date_min,
                $date_max
            );
        } catch (CMbException $e) {
            $e->stepAjax(UI_MSG_ERROR);
        }

        $patients = CMbArray::pluck($patients, '_view');

        array_walk($patients, function (&$item): void {
            $item = mb_convert_encoding($item, 'UTF-8', 'ISO-8859-1');
        });

        $this->renderSmarty(
            'vw_massive_qualify',
            [
                'patients' => $patients,
            ]
        );
    }

    /**
     * @throws CMbModelNotFoundException
     * @throws Exception
     */
    public function qualifyIdentity(): void
    {
        if (!CAppUI::pref("allowed_modify_identity_status")) {
            CAppUI::accessDenied();
        }

        $patient_id  = CView::post('patient_id', 'ref class|CPatient');
        $traits_insi = CView::post('traits_insi', 'str');

        CView::checkin();

        $patient     = CPatient::findOrFail($patient_id);
        $traits_insi = json_decode(stripcslashes($traits_insi), true);

        $qualifier_service = (new PatientQualifierService($patient, $traits_insi));
        $return            = $qualifier_service->qualify();

        $this->renderJson(
            [
                'qualified' => $return,
                'message'   => $qualifier_service->getMessage(),
            ]
        );
    }
}
