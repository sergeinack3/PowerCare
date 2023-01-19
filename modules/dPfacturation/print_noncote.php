<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;

CCanDo::checkEdit();
// Récupération des paramètres
$filter                  = new CPlageconsult();
$filter->_date_min       = CView::get("_date_min", "date default|now", true);
$filter->_date_max       = CView::get("_date_max", "date default|now", true);
$filter->_type_affichage = CView::get("_type_affichage", "str default|1", true);
$chir_id                 = CView::getRefCheckRead("chir", "str", true);
$export_csv              = CView::get("export_csv", "bool default|0");
$lieu_id                 = CView::get("lieu", "ref class|CLieuConsult");

CView::checkin();
CView::enableSlave();

// Filtre sur les praticiens
$listPrat = CConsultation::loadPraticiensCompta($chir_id);

// On recherche toutes les consultations non cotés
$ljoin                 = [];
$ljoin["plageconsult"] = "consultation.plageconsult_id = plageconsult.plageconsult_id";

$where                         = [];
$where["tarif"]                = " IS NULL ";
$where["codes_ccam"]           = " IS NULL";
$where["patient_id"]           = " IS NOT NULL";
$where["secteur1"]             = " = 0";
$where["secteur2"]             = " = 0";
$where["consultation.annule"]  = " = '0'";
$where["plageconsult.chir_id"] = CSQLDataSource::prepareIn(array_keys($listPrat));
$where["plageconsult.date"]    = "BETWEEN '$filter->_date_min' AND '$filter->_date_max'";

$order = "plageconsult.date, plageconsult.chir_id";

$consultation = new CConsultation();
/** @var CConsultation[] $listConsults */
$listConsults = $consultation->loadList($where, $order, null, null, $ljoin);
CStoredObject::massLoadFwdRef($listConsults, "patient_id");
CStoredObject::massLoadFwdRef($listConsults, "plageconsult_id");

$listConsults_date = [];
foreach ($listConsults as $_consult) {
    $_consult->loadRefPatient();
    $_consult->loadRefPlageConsult();
    $agenda = $_consult->_ref_plageconsult->loadRefAgendaPraticien();
    if ($lieu_id && ($lieu_id != $agenda->lieuconsult_id || !$agenda->lieuconsult_id)) {
        continue;
    }
    $listConsults_date[$_consult->_ref_plageconsult->date]["consult"][$_consult->_id] = $_consult;
}

if (!$export_csv) {
    // Création du template
    $smarty = new CSmartyDP();

    $smarty->assign("filter", $filter);
    $smarty->assign("listPrat", $listPrat);
    $smarty->assign("listConsults", $listConsults);
    $smarty->assign("listConsults_date", $listConsults_date);

    $smarty->display("print_noncote");
} else {
    $file = new CCSVFile();
    $file->writeLine(
        [
            CAppUI::tr("CConsultation-_prat_id"),
            CAppUI::tr("CConsultation-patient_id"),
            CAppUI::tr("CPlageconsult-date"),
            CAppUI::tr("CConsultation-heure"),
        ]
    );
    foreach ($listConsults_date as $key_date => $consultations) {
        foreach ($consultations["consult"] as $consultation) {
            $file->writeLine(
                [
                    $consultation->_ref_chir->_view,
                    $consultation->_ref_patient->_view,
                    CMbDT::format($consultation->_ref_plageconsult->date, CAppUI::conf("date")),
                    CMbDT::format($consultation->heure, '%Hh%M'),
                ]
            );
        }
    }

    $file_name = CAppUI::tr("Gestion.print_noncote") . "_";
    if ($chir_id && $listPrat[$chir_id]) {
        $file_name .= $listPrat[$chir_id]->_user_first_name . "_" . $listPrat[$chir_id]->_user_last_name . "_";
    }
    if ($filter->_date_min != $filter->_date_max) {
        $file_name .= CMbDT::format($filter->_date_min, '%d-%m-%Y') . "_" . CAppUI::tr("date.to") . "_" . CMbDT::format(
                $filter->_date_max,
                '%d-%m-%Y'
            );
    } else {
        $file_name .= CMbDT::format($filter->_date_min, '%d-%m-%Y');
    }

    $file->stream($file_name);
    CApp::rip();
}
