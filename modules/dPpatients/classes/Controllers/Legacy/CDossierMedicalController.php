<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Controllers\Legacy;

use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Patients\CDossierMedical;

class CDossierMedicalController extends CLegacyController
{
    public function editGroupeRhesus()
    {
        $this->checkPermEdit();

        $patient_id = CView::get("patient_id", "ref class|CPatient");

        CView::checkin();

        $can_edit_groupe_rhesus = false;
        $mediuser     = CUser::get()->loadRefMediuser();
        if ($mediuser->isPraticien() || $mediuser->isInfirmiere()) {
            $can_edit_groupe_rhesus = true;
        }

        $dossier_medical_id = CDossierMedical::dossierMedicalId($patient_id, "CPatient");
        $dossier_medical = CDossierMedical::findOrFail($dossier_medical_id);

        $this->renderSmarty("vw_edit_groupe_rhesus", [
            'dossier_medical'        => $dossier_medical,
            'patient_id'             => $patient_id,
            'can_edit_groupe_rhesus' => $can_edit_groupe_rhesus,
        ]);
    }
}
