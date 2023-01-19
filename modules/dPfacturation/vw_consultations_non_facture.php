<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();
// Récupération des paramètres
$filter            = new CPlageconsult();
$filter->_date_min = CView::get("_date_min", "date default|now", true);
$filter->_date_max = CView::get("_date_max", "date default|now", true);
$tarif_id          = CView::get("tarif", "str", true);
$chir_id           = CView::getRefCheckRead("chir", "num", true);
$lieu_id           = CView::get("lieu", "ref class|CLieuConsult");

CView::checkin();
CView::enableSlave();

// Filtre sur les praticiens
$praticien = new CMediusers();
$praticien->load($chir_id);
$praticien->loadRefFunction();

// On recherche toutes les consultations sur la période choisie
$ljoin                 = [];
$ljoin["plageconsult"] = "plageconsult.plageconsult_id = consultation.plageconsult_id";

// Tarifs
if ($tarif_id) {
    $tarif = new CTarif();
    $tarif->load($tarif_id);
    $where["consultation.tarif"] = " = '$tarif->description'";
}

$where                            = [];
$where["consultation.patient_id"] = " IS NOT NULL";
$where["consultation.sejour_id"]  = " IS NULL";
$where["consultation.valide"]     = " = '0'";
$where["consultation.annule"]     = " = '0'";
$where["plageconsult.chir_id"]    = " = '$chir_id'";
$where["plageconsult.date"]       = "BETWEEN '$filter->_date_min' AND '$filter->_date_max'";

$order = "plageconsult.date";

$consultation = new CConsultation();
/** @var CPlageconsult[] $plages */
$consultations = $consultation->loadList($where, $order, null, "consultation.consultation_id", $ljoin);
CStoredObject::massLoadFwdRef($consultations, "patient_id");
$plages = CStoredObject::massLoadFwdRef($consultations, "plageconsult_id");
CStoredObject::massLoadFwdRef($plages, "chir_id");

$consultations_patient = [];
foreach ($consultations as $_consult) {
    $patient = $_consult->loadRefPatient();
    $_consult->loadRefsActes();
    $_consult->loadRefPlageConsult();
    $agenda = $_consult->_ref_plageconsult->loadRefAgendaPraticien();
    if ($lieu_id && ($lieu_id != $agenda->lieuconsult_id || !$agenda->lieuconsult_id)) {
        continue;
    }
    $consultations_patient[$patient->_id][$_consult->_id] = $_consult;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("filter", $filter);
$smarty->assign("praticien", $praticien);
$smarty->assign("consults_patient", $consultations_patient);
$smarty->display("vw_consultations_non_facture");
