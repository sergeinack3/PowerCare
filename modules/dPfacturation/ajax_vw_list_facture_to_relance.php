<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;

CCanDo::checkEdit();
$date_min       = CValue::getOrSession("_date_min", CMbDT::date());
$date_max       = CValue::getOrSession("_date_max", CMbDT::date());
$type_relance   = CValue::getOrSession("type_relance", 0);
$facture_class  = CValue::getOrSession("facture_class");
$chirSel        = CValue::getOrSession("chir", "-1");
$page           = CView::get('page', 'num default|0');
$step           = CView::get('step', 'num default|50');

CView::checkin();

$date              = CMbDT::date();
//Récupération des délais avant chaque relance (1ère, 2ème, 3ème).
$delais_relance = array();
$delais_relance[1]  = CAppUI::gconf("dPfacturation CRelance nb_days_first_relance");
$delais_relance[2] = CAppUI::gconf("dPfacturation CRelance nb_days_second_relance");
$delais_relance[3]  = CAppUI::gconf("dPfacturation CRelance nb_days_third_relance");

$type_relance_number_word = array();
$type_relance_number_word[1] = "first";
$type_relance_number_word[2] = "second";
$type_relance_number_word[3] = "third";

/** @var CFactureCabinet|CFactureEtablissement $facture*/
$facture = new $facture_class;
$facture_table = $facture->getSpec()->table;
$ljoin = array();

$ljoin["facture_relance"] = "$facture_table.facture_id = facture_relance.object_id";

$where = array();

// Praticien selectionné
if ($chirSel) {
  $where["praticien_id"] =" = '$chirSel' ";
}
$where["no_relance"] = " = '0'";
$where["annule"]     = " = '0'";
$where["extourne"]   = " = '0'";
$where["regle"]      = " = '0'";

$where["cloture"] = "BETWEEN '$date_min' AND '$date_max'";

$where[] = "($facture_table.du_patient > 0 AND patient_date_reglement IS NULL) OR ($facture_table.du_tiers > 0 AND tiers_date_reglement IS NULL)";

switch ($type_relance) {
  case 1:
    $where["facture_relance.relance_id"] = "IS NULL";
    $where[] = "DATEDIFF('$date', cloture ) >= $delais_relance[$type_relance]";
    break;
  case 2:
  case 3:
    $where["facture_relance.numero"] = "= '".($type_relance-1)."'";
    $where["facture_relance.etat"] = "= 'emise'";
    $where[] = "facture_relance.statut <> 'inactive' OR facture_relance.statut IS NULL";
    $where[] = "DATEDIFF('$date', facture_relance.date ) >= $delais_relance[$type_relance]";
    break;
  default:
    // do nothing
    break;
}

$total_factures = $facture->countList($where , null, $ljoin);
$factures = $facture->loadList($where , "cloture DESC", "$page, $step", "$facture_table.facture_id", $ljoin, null);

foreach ($factures as $key => $_facture) {
  /** @var CFactureCabinet|CFactureEtablissement $_facture */
  $_facture->loadRefPatient();
  $_facture->loadRefsObjects();
  $_facture->loadRefsReglements();
  $_facture->loadStatut();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("factures"                  , $factures);
$smarty->assign("reglement"                 , new CReglement());
$smarty->assign("facture"                   , $facture);
$smarty->assign("total_factures"            , $total_factures);
$smarty->assign("page"                      , $page);
$smarty->assign("step"                      , $step);
$smarty->assign("type_relance"              , $type_relance);
$smarty->assign("type_relance_number_word"  , $type_relance_number_word[$type_relance]);
$smarty->assign("change_page"               , 'ListeFacture.changePage');

$smarty->display("vw_list_factures");