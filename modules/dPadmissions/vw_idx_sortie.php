<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CPrestationPonctuelle;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

// Filtres d'affichage
$sejour = new CSejour();

$selSortis         = CValue::getOrSession("selSortis", "0");
$order_col         = CValue::getOrSession("order_col", "patient_id");
$order_way         = CValue::getOrSession("order_way", "ASC");
$date              = CValue::getOrSession("date", CMbDT::date());
$services_ids      = CValue::getOrSession("services_ids");
$sejours_ids       = CValue::getOrSession("sejours_ids");
$prat_id           = CValue::getOrSession("prat_id");
$only_confirmed    = CValue::getOrSession("only_confirmed");
$period            = CValue::getOrSession("period");
$filterFunction    = CValue::getOrSession("filterFunction");
$enabled_service   = CValue::getOrSession("active_filter_services", 0);
$date_actuelle     = CMbDT::dateTime("00:00:00");
$date_demain       = CMbDT::dateTime("00:00:00", "+ 1 day");
$hier              = CMbDT::date("- 1 day", $date);
$demain            = CMbDT::date("+ 1 day", $date);
$type_pec          = CView::get("type_pec", array('str', 'default' => $sejour->_specs["type_pec"]->_list), true);
$circuits_ambu     = CView::get("circuit_ambu", array('str', 'default' => $sejour->_specs["circuit_ambu"]->_list), true);
$prestations_p_ids = CView::get("prestations_ids", "str", true);

$services_ids = CService::getServicesIdsPref($services_ids);
$sejours_ids  = CSejour::getTypeSejourIdsPref($sejours_ids);

// Récupération de la liste des praticiens
$prat  = CMediusers::get();
$prats = $prat->loadPraticiens();

$sejour               = new CSejour();
$sejour->praticien_id = $prat_id;

// Liste des types d'admission possibles
$list_type_admission = $sejour->_specs["_type_admission"]->_list;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("date_demain", $date_demain);
$smarty->assign("date_actuelle", $date_actuelle);
$smarty->assign("date", $date);
$smarty->assign("selSortis", $selSortis);
$smarty->assign("order_way", $order_way);
$smarty->assign("order_col", $order_col);
$smarty->assign("prats", $prats);
$smarty->assign("hier", $hier);
$smarty->assign("demain", $demain);
$smarty->assign("period", $period);
$smarty->assign("only_confirmed", $only_confirmed);
$smarty->assign("filterFunction", $filterFunction);
$smarty->assign("list_type_ad", $list_type_admission);
$smarty->assign('enabled_service', $enabled_service);
$smarty->assign("type_pec", $type_pec);
$smarty->assign('circuits_ambu', $circuits_ambu);
$smarty->assign("prestations_ponctuelles", CPrestationPonctuelle::loadCurrentList());
$smarty->assign("prestations_p_ids", $prestations_p_ids);

$smarty->display("vw_idx_sortie.tpl");
