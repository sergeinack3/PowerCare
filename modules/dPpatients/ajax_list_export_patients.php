<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$dateMin       = CView::get("_date_min", "date default|" . CMbDT::date("-1 month"), true);
$dateMax       = CView::get("_date_max", "date default|now", true);
$prat_selected = CView::getRefCheckRead("praticien_id", "ref class|CMediusers", true);
$function_id   = CView::getRefCheckRead("function_id", "ref class|CFunctions", true);
$page          = CView::get("page", "num default|0");
$export        = CView::get("export", "num default|0");
CView::checkin();

$filter            = new CConsultation();
$filter->_date_min = $dateMin;
$filter->_date_max = $dateMax;

$patients  = new CPatient();
$resultats = [];
$count     = 0;

if ($function_id || $prat_selected) {
    $ljoin                 = [];
    $ljoin["consultation"] = "consultation.patient_id = patients.patient_id";
    $ljoin["plageconsult"] = "plageconsult.plageconsult_id = consultation.plageconsult_id";

    $where = [];
    if ($prat_selected) {
        $where["plageconsult.chir_id"] = "= '$prat_selected'";
    } elseif ($function_id) {
        $function      = CFunctions::findOrFail($function_id);
        $praticiens_id = implode(',', CMbArray::pluck($function->loadRefsUsers(), "_id"));

        $where["plageconsult.chir_id"] = ($praticiens_id) ? "IN ($praticiens_id)" : "= '0'";
    }

    $where["consultation.annule"]     = "= '0'";
    $where["consultation.patient_id"] = "IS NOT NULL";
    $where["plageconsult.date"]       = " BETWEEN '$dateMin' AND '$dateMax'";

    if (CAppUI::isCabinet()) {
        if (!$function_id && $prat_selected) {
            $function_id = CMediusers::findOrFail($prat_selected)->function_id;
        }

        $where["patients.function_id"] = "= '$function_id'";
    } elseif (CAppUI::isGroup()) {
        $where["patients.group_id"] = "= '" . CGroups::get()->_id . "'";
    }


    $order = "patients.nom ASC";

    $limit = null;
    if (!$export) {
        $limit = "$page, 50";
    }

    $groupby = "patients.nom, patients.prenom";

    $resultats     = $patients->loadList($where, $order, $limit, $groupby, $ljoin);
    $resultats_ids = $patients->loadIds($where, $order, null, $groupby, $ljoin);
    $count         = count($resultats_ids);

    foreach ($resultats as $_patient) {
        $_patient->loadRefsCorrespondants();
    }
}

if ($export) {
    $pays    = CAppUI::conf("ref_pays");
    $csvfile = new CCSVFile();
    $titles  = [
        CAppUI::tr("CPatient-nom"),
        CAppUI::tr("CPatient-prenom"),
        CAppUI::tr("CPatient-sexe"),
        CAppUI::tr("CPatient-_age"),
        CAppUI::tr("CPatient-naissance"),
        CAppUI::tr("CPatient-rques"),
        $pays == 1 ? CAppUI::tr("CPatient-matricule") : CAppUI::tr("CPatient-avs"),
        CAppUI::tr("CPatient-adresse"),
        CAppUI::tr("CPatient-ville"),
        CAppUI::tr("CPatient-tel"),
        CAppUI::tr("CPatient-tel2"),
        CAppUI::tr("CPatient-email"),
        CAppUI::tr("CPatient-medecin_traitant"),
    ];
    $csvfile->writeLine($titles);

    foreach ($resultats as $_patient) {
        $_line = [
            $_patient->nom_jeune_fille ? $_patient->nom . ' (' . $_patient->nom_jeune_fille . ')' : $_patient->nom,
            $_patient->prenom,
            $_patient->sexe,
            $_patient->_age,
            $_patient->getFormattedValue("naissance"),
            $_patient->rques ? substr($_patient->rques, 0, 40) . "..." : "",
            $pays == 1 ? $_patient->matricule : $_patient->avs,
            $_patient->adresse,
            $_patient->cp . ' ' . $_patient->ville,
            $_patient->tel,
            $_patient->tel2,
            $_patient->email,
            $_patient->_ref_medecin_traitant->_shortview,
        ];

        $csvfile->writeLine($_line);
    }

    $csvfile->stream(
        "Listes des patients - " . CMbDT::format($dateMin, "%d-%m-%Y") . " au " . CMbDT::format($dateMax, "%d-%m-%Y")
    );
} else {
    // smarty
    $smarty = new CSmartyDP();
    $smarty->assign("patients", $resultats);
    $smarty->assign("filter", $filter);
    $smarty->assign("page", $page);
    $smarty->assign("count", $count);
    $smarty->display("inc_list_export_patients.tpl");
}
