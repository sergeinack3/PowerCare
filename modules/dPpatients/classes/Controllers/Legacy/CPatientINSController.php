<?php

namespace Ox\Mediboard\Patients\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\OpenData\CCommuneFrance;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPaysInsee;
use Ox\Mediboard\Patients\CPatientINSNIR;

class CPatientINSController extends CLegacyController
{
    public function showDatamatrixIns()
    {
        $this->checkPermEdit();

        $patient_id = CView::get("patient_id", "ref class|CPatient");

        CView::checkin();

        $patient = CPatient::findOrFail($patient_id);
        $patient->loadRefPatientINSNIR();
        $data = $patient->_ref_patient_ins_nir->createDataForDatamatrix();
        $patient->_ref_patient_ins_nir->createDatamatrix($data);

        $this->renderSmarty(
            'vw_datamatrix_ins.tpl',
            [
                'patient' => $patient,
            ]
        );
    }

    public function openModalReadDatamatrixINS()
    {
        $this->checkPermEdit();

        $search = CView::get("search", "bool default|1");

        CView::checkin();

        $this->renderSmarty(
            'vw_read_datamatrix_ins.tpl',
            [
                'search' => $search,
            ]
        );
    }

    public function readDatamatrixINS()
    {
        $this->checkPermEdit();

        $ins    = CView::get("ins", "str");
        $search = CView::get("search", "bool default|1");

        CView::checkin();

        if ($ins == "") {
            CAppUI::setMsg('INS vide', UI_MSG_ERROR);
            echo CAppUI::getMsg();

            return;
        }

        $patient_insnir = new CPatientINSNIR();
        $data           = $patient_insnir->readDatamatrixINS($ins);
        if ($search) {
            CAppUI::callbackAjax("INS.fillFormSearchPatient", $data);

            return;
        }
        $first_space            = strpos($data["prenoms"], " ");
        $data["premier_prenom"] = $data["prenoms"];
        if ($first_space) {
            $data["premier_prenom"] = substr($data["prenoms"], 0, $first_space);
        }

        CAppUI::callbackAjax("INS.createPatient", $data);

        return;
    }
}
