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
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPlageConge;

CCanDo::checkEdit();

// Période
$filter            = new CPlageconsult();
$filter->_date_min = CView::get("_date_min", "date", true);
$filter->_date_max = CView::get("_date_max", "date", true);
$chir_id           = CView::getRefCheckRead("chir", "ref class|CMediusers", true);
$export_csv        = CView::get("export_csv", "bool default|0");
$lieu_id           = CView::get("lieu", "ref class|CLieuConsult");

CView::checkin();
CView::enableSlave();

// Tri sur les praticiens
$mediuser = CMediusers::get();
$mediuser->loadRefFunction();

$prat = CMediusers::get($chir_id);

if (!$prat->_id) {
    CAppUI::stepMessage(UI_MSG_WARNING, "CMediusers-warning-undefined");

    return;
}

$prat->loadRefFunction();
$listPrat     = [$prat->_id => $prat];
$wherePrat    = $prat->getUserSQLClause();
$whereNotPrat = "<> '$prat->_id'";
if (count($prat->_ref_secondary_users)) {
    foreach ($prat->_ref_secondary_users as $_user) {
        $listPrat[$_user->_id] = $_user;
    }
    $whereNotPrat = CSQLDataSource::prepareNotIn(array_keys($listPrat));
}

$ljoin                 = [];
$ljoin["consultation"] = "consultation.plageconsult_id = plageconsult.plageconsult_id";

$where = [];
$query = "
  (plageconsult.chir_id $whereNotPrat AND
    (plageconsult.remplacant_id $wherePrat OR plageconsult.pour_compte_id $wherePrat))
  OR
  (plageconsult.chir_id $wherePrat AND
    ((plageconsult.remplacant_id $whereNotPrat AND plageconsult.remplacant_id IS NOT NULL)
      OR
     (plageconsult.pour_compte_id $whereNotPrat AND plageconsult.pour_compte_id IS NOT NULL))
   )";

if (CAppUI::gconf("personnel global see_retrocession")) {
    $ljoin["plageconge"] = "plageconge.user_id = plageconsult.chir_id OR plageconge.replacer_id = plageconsult.chir_id";
    $query               .= "
   OR (
    plageconge.replacer_id IS NOT NULL
    AND plageconge.date_debut <= '$filter->_date_max 23:59:59'
    AND plageconge.date_fin >= '$filter->_date_min 00:00:00'
    AND (plageconge.user_id $wherePrat OR plageconge.replacer_id $wherePrat)
    AND plageconsult.chir_id $wherePrat
    AND plageconsult.remplacant_id IS NULL AND plageconsult.pour_compte_id IS NULL
    AND (plageconsult.date BETWEEN DATE(plageconge.date_debut) AND DATE(plageconge.date_fin))
   )";
}

$where[]                      = $query;
$where["plageconsult.date"]   = " BETWEEN '$filter->_date_min' AND '$filter->_date_max'";
$where["consultation.annule"] = "= '0'";
$order                        = "chir_id ASC, plageconsult.date";
$plageconsult                 = new CPlageconsult();
$listPlages                   = $plageconsult->loadList($where, $order, null, "plageconsult_id", $ljoin);

CStoredObject::massLoadFwdRef($listPlages, "chir_id");
$consultations = CStoredObject::massLoadBackRefs($listPlages, "consultations", null, ["annule" => "= '0'"]);
CStoredObject::massLoadFwdRef($consultations, "patient_id");
CStoredObject::massLoadFwdRef($listPlages, "remplacant_id");
CStoredObject::massLoadFwdRef($listPlages, "pour_compte_id");

$plages = [];
$totaux = [];
$conge  = new CPlageConge();
/* @var CPlageConsult[] $listPlages */
foreach ($listPlages as $plage) {
    $plage->loadRefsConsultations(false);
    $plage->loadRefChir();
    $plage->loadRefRemplacant();
    $plage->loadRefPourCompte();
    $agenda = $plage->loadRefAgendaPraticien();
    if ($lieu_id && ($lieu_id != $agenda->lieuconsult_id || !$agenda)) {
        continue;
    }

    $plages[$plage->_id]["total"] = 0;
    if (!$plage->remplacant_id && !$plage->pour_compte_id) {
        $conge->loadFor($plage->chir_id, $plage->date);
        $plage->chir_id         = $conge->user_id;
        $plage->_ref_chir       = $conge->loadRefUser();
        $plage->remplacant_id   = $conge->replacer_id;
        $plage->_ref_remplacant = $conge->loadRefReplacer();
        $plage->_ref_remplacant->loadRefFunction();
        $plage->pct_retrocession = $conge->pct_retrocession;
    }

    CStoredObject::massLoadFwdRef($plage->_ref_consultations, "patient_id");

    foreach ($plage->_ref_consultations as $consult) {
        $consult->loadRefPatient();
        $retrocession = $consult->du_patient * $plage->pct_retrocession / 100;
        @$totaux[$plage->chir_id][0] = $plage->_ref_chir;
        @$totaux[$plage->chir_id]["total"] += $consult->du_patient;
        @$totaux[$plage->chir_id]["retrocession"] += $retrocession;
        $plages[$plage->_id]["total"] += $retrocession;
    }
}

if (!$export_csv) {
    // Création du template
    $smarty = new CSmartyDP();
    $smarty->assign("listPrat", $listPrat);
    $smarty->assign("listPlages", $listPlages);
    $smarty->assign("filter", $filter);
    $smarty->assign("plages", $plages);
    $smarty->assign("totaux", $totaux);
    $smarty->assign("totaux", $totaux);
    $smarty->display("print_retrocession");
} else {
    $file = new CCSVFile();
    $file->writeLine(
        [
            CAppUI::tr("CConsultation-patient_id"),
            CAppUI::tr("CConsultation-_prat_id"),
            CAppUI::tr("CPlageConsult.remplacement_of") . " / " . CAppUI::tr("CPlageconsult-pour_compte_id"),
            CAppUI::tr("CPlageconsult-date"),
            CAppUI::tr("CConsultation-heure"),
            CAppUI::tr("CConsultation-tarif"),
            CAppUI::tr("CReglement-montant"),
            CAppUI::tr("CFactureCabinet-_montant_retrocession"),
            CAppUI::tr("CPlageconsult-pct_retrocession"),
        ]
    );

    //Lignes de remplacements
    foreach ($listPlages as $_plage) {
        foreach ($_plage->_ref_consultations as $_consultation) {
            $praticien    = $_plage->_ref_remplacant->_id ? $_plage->_ref_remplacant : $_plage->_ref_chir;
            $prat_origine = $_plage->_ref_remplacant->_id ? $_plage->_ref_chir : $_plage->_ref_pour_compte;
            $file->writeLine(
                [
                    $_consultation->_ref_patient->_view,
                    $praticien->_view,
                    $prat_origine->_view,
                    CMbDT::format($_plage->date, CAppUI::conf("date")),
                    CMbDT::format($_consultation->heure, '%Hh%M'),
                    $_consultation->tarif,
                    $_consultation->du_patient,
                    round($_consultation->du_patient * $_plage->pct_retrocession / 100, 2),
                    $_plage->pct_retrocession . "%",
                ]
            );
        }
    }

    $file_name = CAppUI::tr("Gestion.print_retrocession") . "_";
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
