<?php


namespace Ox\Mediboard\Patients\Controllers\Legacy;


use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CLegacyController;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;

class CConsentementController extends CLegacyController
{
    public function ajax_count_consentement()
    {
        CCanDo::checkAdmin();

        $tag          = CView::get("tag", "str");
        $consentement = CView::get("consentement", "bool");

        CView::checkin();

        $where                                    = [];
        $where["patients.allow_sms_notification"] = "!= '$consentement'";
        $where["id_sante400.tag"]                 = "= '$tag'";
        $where["id_sante400.object_class"]        = "= 'CPatient'";

        $ljoin                = [];
        $ljoin["id_sante400"] = "patients.patient_id = id_sante400.object_id";

        $patient       = new CPatient();
        $count_patient = $patient->countList($where, null, $ljoin);

        CAppUI::stepAjax(CAppUI::tr("CPatient-msg-change-consentement-count", $count_patient));
    }

    public function ajax_edit_consentement()
    {
        CCanDo::checkAdmin();

        $tag          = CView::get("tag", "str");
        $consentement = CView::get("consentement", "bool");

        CView::checkin();

        $where                                    = [];
        $where["patients.allow_sms_notification"] = "!= '$consentement'";
        $where["id_sante400.tag"]                 = "= '$tag'";
        $where["id_sante400.object_class"]        = "= 'CPatient'";

        $ljoin                = [];
        $ljoin["id_sante400"] = "patients.patient_id = id_sante400.object_id";

        $patient       = new CPatient();
        $count_patient = $patient->countList($where, null, $ljoin);

        $query = "UPDATE patients
            LEFT JOIN id_sante400
            ON patients.patient_id = id_sante400.object_id
            SET allow_sms_notification = '" . $consentement . "'
            WHERE id_sante400.tag = '" . $tag . "'
            AND patients.allow_sms_notification != '" . $consentement . "';";

        $ds = CSQLDataSource::get("std");

        if ($ds->exec($query)) {
            CAppUI::setMsg(CAppUI::tr('CPatient-msg-edit-consentement'), UI_MSG_OK);
        } else {
            CAppUI::setMsg(CAppUI::tr('Error'), UI_MSG_WARNING);
        }

        echo CAppUI::getMsg();
    }

    public function ajax_vw_consentement()
    {
        CCanDo::checkAdmin();

        $patient = new CPatient();
        $idex    = new CIdSante400();

        $smarty = new CSmartyDP();
        $smarty->assign("patient", $patient);
        $smarty->assign("idex", $idex);
        $smarty->display("inc_vw_consentement");
    }

}
