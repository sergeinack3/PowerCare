<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFactureCabinet;

CCanDo::checkEdit();

// Récupération des paramètres
$date_min = CView::get("_date_min", "date default|now", true);
$date_max = CView::get("_date_max", "date default|now", true);
$chir_id  = CView::get("chir", "ref class|CMediusers", true);

CView::checkin();
CView::enableSlave();

$taux_factures = array();
$list_taux = array();

// Filtre sur les praticiens
$listPrat = CConsultation::loadPraticiensCompta($chir_id);

$where = array();
$where["ouverture"] = "BETWEEN '$date_min 00:00:00' AND '$date_max 23:59:59'";
$where["facture_cabinet.praticien_id"] = CSQLDataSource::prepareIn(array_keys($listPrat));

$facture = new CFactureCabinet();

$list_taux = explode("|", CAppUI::gconf("dPcabinet CConsultation default_taux_tva"));
foreach ($list_taux as $taux) {
  $where["taux_tva"] = " = '$taux'";
  $factures = $facture->loadGroupList($where, "ouverture, praticien_id");

  $patients = CStoredObject::massLoadFwdRef($factures, "patient_id");
  CStoredObject::massLoadBackRefs($patients, "correspondants_patient", "date_debut DESC, date_fin DESC");

  CStoredObject::massLoadFwdRef($factures, "praticien_id");
  CStoredObject::massLoadFwdRef($factures, "coeff_id");
  CStoredObject::massLoadBackRefs($factures, "reglements");
  CStoredObject::massLoadBackRefs($factures, "items", "date ASC, code ASC");
  CStoredObject::massLoadBackRefs($factures, "relance_fact", "date");
  $taux_factures[$taux] = $factures;
}

$total_tva = 0;
$nb_factures = 0;
foreach ($taux_factures as $taux => $factures) {
  $nb_factures += count($factures);
  $total = $totalht = $totalttc = $totalst = 0;

  foreach ($factures as $facture) {
    $facture->loadRefPatient();
    $facture->loadRefPraticien();
    $facture->loadRefsObjects();
    $facture->loadRefsReglements();

    $total    += $facture->du_tva;
    $totalht  += ($facture->_montant_avec_remise - $facture->du_tva);
    $totalttc += $facture->_montant_avec_remise;
    $totalst  += $facture->_secteur3;
  }

  $taux_factures[$taux] = array();
  $taux_factures[$taux]["count"] = count($factures);
  $taux_factures[$taux]["total"] = $total;
  $taux_factures[$taux]["factures"] = $factures;
  $taux_factures[$taux]["totalst"]  = $totalst;
  $taux_factures[$taux]["totalht"]  = $totalht;
  $taux_factures[$taux]["totalttc"] = $totalttc;

  $total_tva += $total;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("taux_factures", $taux_factures);
$smarty->assign("total_tva",     $total_tva);
$smarty->assign("nb_factures",   $nb_factures);
$smarty->assign("list_taux",     $list_taux);
$smarty->assign("date_min",      $date_min);
$smarty->assign("date_max",      $date_max);
$smarty->assign("listPrat",      $listPrat);

$smarty->display("print_tva");
