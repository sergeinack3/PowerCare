<?php
/**
 * @package Mediboard\Facturation
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
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkEdit();
$facture = new CFactureCabinet();
$rectif                = CMbDT::transform("+0 DAY", CMbDT::date(), "%d")-1;
$month_deb             = CMbDT::date("-$rectif DAYS"  , CMbDT::date());
$date_min              = CView::get("_date_min", "date default|".$month_deb, true);
$date_max              = CView::get("_date_max", "date default|now", true);
$etat                  = CView::get("etat", "str default|ouvert", true);
$patient_id            = CView::get("patient_id", "ref class|CPatient", true);
$type_date_search      = CView::get("type_date_search", "enum list|cloture|ouverture default|cloture", true);
$chir_sel              = CView::get("chirSel", "str", true);
$num_facture           = CView::get("num_facture", "str", true);
$numero                = CView::get("numero", "enum list|0|1|2|3 default|0", true);
$search_easy           = CView::get("search_easy", "str default|0", true);
$xml_etat              = CView::get("xml_etat", "enum list|echec|non_envoye|envoye", true);
$facture->statut_pro   = CView::get("statut_pro", "enum list|".$facture->_specs["statut_pro"]->list, true);
$page                  = CView::get("page", "num default|0");
$print                 = CView::get("print", "num default|0");
CView::checkin();

// Liste des chirurgiens
$user = new CMediusers();
$list_chir =  $user->loadPraticiens(PERM_EDIT);
$list_all_chir =  $user->loadPraticiens(PERM_EDIT, null, null, null, false);

//Patient sélectionné
$patient = new CPatient();
$patient->load($patient_id);

$factures = array();
$total_factures = 0;

$filter = new CConsultation();
$filter->_date_min = $date_min;
$filter->_date_max = $date_max;

// Création du template
$smarty = new CSmartyDP("modules/dPfacturation");

$smarty->assign("tab"           , "vw_factures_cabinet");
$smarty->assign("factures"      , $factures);
$smarty->assign("listChirs"     , $list_chir);
$smarty->assign("listAllChirs"  , $list_all_chir);
$smarty->assign("chirSel"       , $chir_sel);
$smarty->assign("patient"       , $patient);
$smarty->assign("facture"       , $facture);
$smarty->assign("date"          , CMbDT::date());
$smarty->assign("filter"        , $filter);
$smarty->assign("type_date_search", $type_date_search);
$smarty->assign("num_facture"   , $num_facture);
$smarty->assign("numero"        , $numero);
$smarty->assign("search_easy"   , $search_easy);
$smarty->assign("page"          , $page);
$smarty->assign("total_factures", $total_factures);
$smarty->assign("xml_etat"      , $xml_etat);
$smarty->assign("print"         , $print);

if ($print) {
  $smarty->display("inc_list_factures");
}
else {
  if (CAppUI::isCabinet() || CAppUI::isGroup()) {
    $group = CGroups::loadCurrent();
    $group->loadFunctions();
    $function_limitation =
      "function_id IN (" . implode(",", CMbArray::pluck($group->_ref_functions, "function_id"))
      . ") OR function_id IS NULL";
    $smarty->assign("function_limitation", $function_limitation);
  }

  $smarty->display("vw_factures");
}
