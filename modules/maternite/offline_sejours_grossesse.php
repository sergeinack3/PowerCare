<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CGrossesse;

CCanDo::checkRead();
$mois = CView::get("mois", "num default|0");
CView::checkin();
CView::enforceSlave();

$number_mois = $mois >= 0 ? "+ $mois" : "$mois";
$date_min    = CMbDT::date("first day of $number_mois month");
$date_max    = CMbDT::date("last day of this month", $date_min);

$ljoin = array("patients" => "patients.patient_id = grossesse.parturiente_id");

$where = array(
  "grossesse.terme_prevu" => "BETWEEN '$date_min' AND '$date_max'",
  "grossesse.active"      => "= '1'"
);

$grossesse = new CGrossesse();
$grossesses = $grossesse->loadGroupList($where,"nom, prenom", null, null, $ljoin);

CStoredObject::massLoadFwdRef($grossesses, "parturiente_id");

$ljoin = array(
  "plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id"
);

$consultations = CStoredObject::massLoadBackRefs($grossesses, "consultations", "date DESC, heure DESC", null, $ljoin);
$dossiers_anesth = CStoredObject::massLoadBackRefs($consultations, "consult_anesth");
CStoredObject::massLoadFwdRef($dossiers_anesth, "operation_id");
CStoredObject::massLoadBackRefs($grossesses, "echographies");
$sejours = CStoredObject::massLoadBackRefs($grossesses, "sejours", "entree_prevue DESC");
CStoredObject::massLoadBackRefs($sejours, "operations", "date ASC");

$fiches_anesth = array();
$params_fiches_anesth = array(
  "dossier_anesth_id" => "",
  "operation_id"      => "",
  "offline"           => 1,
  "print"             => 1,
  "pdf"               => 0
);

$suivi_grossesse = array();
$params_suivi_grossesse = array(
  "grossesse_id" => "",
  "offline"      => 1
);

$fiche_synthese = array();
$params_fiche_synthese = array(
  "grossesse_id" => "",
  "offline"      => 1
);

$params_fiche_synthese_bis = array(
  "m" => "maternite",
  "dialog" => "print_fiche_synthese",
  "grossesse_id" => "",
  "offline"      => 1
);

// Echograhy
$list_children = array();

foreach ($grossesses as $_grossesse) {
  $_grossesse->loadRefParturiente();
  $_grossesse->loadRefDossierPerinat();
  $echographies = $_grossesse->loadRefsSurvEchographies();
  $_grossesse->countRefSejours();
  $_grossesse->loadRefsSejours();
  $consultations = $_grossesse->loadRefsConsultations();

  foreach ($consultations as $_consult) {
    // Fiches d'anesthésie
    foreach ($_consult->loadRefsDossiersAnesth() as $_dossier_anesth) {
      $params_fiches_anesth["dossier_anesth_id"] = $_dossier_anesth->_id;
      $fiches_anesth[$_grossesse->_id][$_dossier_anesth->_id] = CApp::fetch("dPcabinet", "print_fiche", $params_fiches_anesth);
    }
  }

  // Suivi de grossesse
  $params_suivi_grossesse["grossesse_id"] = $_grossesse->_id;
  $suivi_grossesse[$_grossesse->_id] = CApp::fetch("maternite", "ajax_vw_liste_suivis_grossesse", $params_suivi_grossesse);

  // Fiche de synthese
  $params_fiche_synthese["grossesse_id"] = $_grossesse->_id;
  $fiche_synthese[$_grossesse->_id] = CApp::fetch("maternite", "print_fiche_synthese", $params_fiche_synthese);



  foreach ($echographies as $_echographie) {
    $_echographie->getSA();

    if ($grossesse->multiple) {
      $list_children[$_grossesse->_id][$_echographie->num_enfant][$_echographie->_id] = $_echographie;
    }
    else {
      $list_children[$_grossesse->_id]["1"][$_echographie->_id] = $_echographie;
    }
  }
}

$graphs = "lcc|cn|bip|pc|pa|lf|poids_foetal";
$list_graphs = explode("|", $graphs);

$smarty = new CSmartyDP();
$smarty->assign("date_min"       , $date_min);
$smarty->assign("date_max"       , $date_max);
$smarty->assign("grossesses"     , $grossesses);
$smarty->assign("fiches_anesth"  , $fiches_anesth);
$smarty->assign("suivi_grossesse", $suivi_grossesse);
$smarty->assign("fiche_synthese" , $fiche_synthese);
$smarty->assign("list_children"  , $list_children);
$smarty->assign("graphs"         , $graphs);
$smarty->assign("list_graphs"    , $list_graphs);
$smarty->display("offline_sejours_grossesse");
