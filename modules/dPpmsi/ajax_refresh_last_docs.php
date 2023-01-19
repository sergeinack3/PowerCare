<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

$cat_docs        = CValue::getOrSession("cat_docs");
$specialite_docs = CValue::getOrSession("specialite_docs");
$prat_docs       = CValue::getOrSession("prat_docs");
$date_docs_min   = CValue::getOrSession("date_docs_min");
$date_docs_max   = CValue::getOrSession("date_docs_max");
$entree_min      = CValue::getOrSession("entree_min");
$entree_max      = CValue::getOrSession("entree_max");
$sortie_min      = CValue::getOrSession("sortie_min");
$sortie_max      = CValue::getOrSession("sortie_max");
$intervention_min = CValue::getOrSession("intervention_min");
$intervention_max = CValue::getOrSession("intervention_max");
$prat_interv     = CValue::getOrSession("prat_interv");
$section_search  = CValue::getOrSession("section_search");
$type            = CValue::getOrSession("type");
$page            = CValue::get("page");

$docs  = array();
$where = array();
$ljoin = array();
$cr    = new CCompteRendu;
$long_period = CMbDT::daysRelative($date_docs_min, $date_docs_max) > 10;

$total_docs = 0;

if (($cat_docs || $specialite_docs || $prat_docs || ($date_docs_min && $date_docs_max)) && !$long_period) {
  
  switch ($section_search) {
    case "sejour":
      $ljoin["sejour"] = "sejour.sejour_id = compte_rendu.object_id OR sejour.sejour_id IS NULL";
      $where["compte_rendu.object_class"] = "= 'CSejour'";

      if ($type) {
        $where["sejour.type"] = "= '$type'";
      }
      if ($entree_min) {
        $where[] = "sejour.entree >= '$entree_min 00:00:00'";
      }

      if ($entree_max) {
        $where[] = "sejour.entree <= '$entree_max 23:59:59'";
      }
      
      if ($sortie_min) {
        $where[] = "sejour.sortie >= '$sortie_min 00:00:00'";
      }

      if ($sortie_max) {
        $where[] = "sejour.sortie <= '$sortie_max '23:59:59'";
      }
      
      break;

    case "intervention":
      $ljoin["operations"] = "operations.operation_id = compte_rendu.object_id OR operations.operation_id IS NULL";
      $where["compte_rendu.object_class"] = "= 'COperation'";
      if ($intervention_min || $intervention_max) {
        if ($intervention_min) {
          $where[] = "operations.date >= '$intervention_min'";
        }
        if ($intervention_max) {
          $where[] = "operations.date <= '$intervention_max'";
        }
      }
      if ($prat_interv) {
        $where["operations.chir_id"] = "= '$prat_interv'";
      }
  }
  
  if ($cat_docs) {
    $where["file_category_id"] = " = '$cat_docs'";
  }
  
  if ($date_docs_min && $date_docs_max) {
    $ljoin["user_log"] = "compte_rendu.compte_rendu_id = user_log.object_id
      AND user_log.object_class = 'CCompteRendu'
      AND user_log.type = 'create'";
    $where["user_log.date"] = "BETWEEN '$date_docs_min 00:00:00' AND '$date_docs_max 23:59:59'";
  }
  
  if ($prat_docs) {
    $where["author_id"] = " = '$prat_docs'";
  }
  else if ($specialite_docs) {
    if (!isset($ljoin["user_log"])) {
      $ljoin["user_log"] = "compte_rendu.compte_rendu_id = user_log.object_id
       AND user_log.object_class = 'CCompteRendu'
       AND user_log.type = 'create'";
    }
    $ljoin["users_mediboard"] = "user_log.user_id = users_mediboard.user_id";
    $where["users_mediboard.function_id"] = " = '$specialite_docs'";
  }
  
  $total_docs = $cr->countList($where, null, $ljoin);
  /** @var CCompteRendu[] $docs */
  $docs = $cr->loadList($where, "user_log.date desc", "$page, 30", null, $ljoin);

  switch ($section_search) {
    case "sejour":
      $sejours = CMbObject::massLoadFwdRef($docs, "object_id", "CSejour");
      CMbObject::massLoadFwdRef($sejours, "patient_id");
      
      foreach ($docs as $_doc) {
        /** @var CSejour $sejour */
        $sejour = $_doc->loadTargetObject();
        $sejour->loadRefPatient();
        $sejour->loadNDA();
        $sejour->loadRefsOperations();
        $_doc->_date = $_doc->loadFirstLog()->date;
      }
      break;
    case "intervention";
      $operations = CMbObject::massLoadFwdRef($docs, "object_id", "COperation");
      $sejours    = CMbObject::massLoadFwdRef($operations, "sejour_id");
      $prats      = CMbObject::massLoadFwdRef($operations, "chir_id");
      CMbObject::massLoadFwdRef($sejours, "patient_id");
      CMbObject::massLoadFwdRef($prats, "function_id");
      
      foreach ($docs as $_doc) {
        /** @var COperation $operation */
        $operation = $_doc->loadTargetObject();
        $operation->loadExtCodesCCAM();
        $operation->loadRefPlageOp();
        $operation->loadRefPatient();
        $chir = $operation->loadRefChir();
        $chir->loadRefFunction();
        $_doc->_date = $_doc->loadFirstLog()->date;
      }
  }
}

$smarty = new CSmartyDP;

$smarty->assign("cat_docs"       , $cat_docs);
$smarty->assign("specialite_docs", $specialite_docs);
$smarty->assign("prat_docs"      , $prat_docs);
$smarty->assign("date_docs_min"  , $date_docs_min);
$smarty->assign("date_docs_max"  , $date_docs_max);
$smarty->assign("docs"           , $docs);
$smarty->assign("long_period"    , $long_period);
$smarty->assign("page"           , $page);
$smarty->assign("total_docs"     , $total_docs);
$smarty->assign("section_search" , $section_search);

$smarty->display("inc_refresh_last_docs");
