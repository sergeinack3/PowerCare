<?php

/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CReadFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Pmsi\CRelancePMSI;

CCanDo::checkRead();

$date_min = CView::get("date_min", "date default|".CMbDT::date('-1 day'), true);
$date_max = CView::get("date_max", "date default|now", true);
$page     = CView::get("pageUrg", "num default|0");
$types    = CView::get("types", "str", true);
CView::checkin();

$operation = new COperation;
$where = array();
$ljoin = array();
$ljoin["sejour"] = "sejour.sejour_id = operations.sejour_id";
$where["operations.date"]       = "BETWEEN '$date_min' AND '$date_max'";
$where["operations.plageop_id"] = "IS NULL";
$where["operations.annulee"]    = "= '0'";
$where["sejour.group_id"]       = "= '".CGroups::loadCurrent()->_id."'";
if ($types && !in_array("", $types)) {
  $where["sejour.type"]         = CSQLDataSource::prepareIn($types);
}
$order = "operations.chir_id";
$step              = 30;
$limit             = "$page,$step";

/** @var COperation[] $horsplages */
$count               = $operation->countList($where, null, $ljoin);
$horsplages = $operation->loadList($where, $order, $limit, null, $ljoin);
/** @var CSejour[] $sejours */
$sejours = COperation::massLoadFwdRef($horsplages, "sejour_id");
/** @var CPatient[] $patients */
$patients = CSejour::massLoadFwdRef($sejours, "patient_id");
CSejour::massLoadNDA($sejours);
CPatient::massLoadIPP($patients);
CSejour::massCountDocItems($sejours);
COperation::massCountDocItems($horsplages);
$chirurgiens = COperation::massLoadFwdRef($horsplages, "chir_id");
CMediusers::massLoadFwdRef($chirurgiens, "function_id");

$counter_type_documents = array();

foreach ($horsplages as $_operation) {
  $_operation->loadRefChir()->loadRefFunction();
  $_operation->loadExtCodesCCAM();
  $operation_docs = $_operation->loadRefsDocItems();

  $_operation->_ref_sejour = $sejours[$_operation->sejour_id];
  $_operation->_ref_sejour->_ref_patient = $patients[$_operation->_ref_sejour->patient_id];
  $sejour_docs = $_operation->_ref_sejour->loadRefsDocItems();

  // count docs by category
  foreach (CRelancePMSI::$docs as $_doc) {
    $counter_type_documents[$_operation->sejour_id][$_doc]["counter"]       = 0;
    $counter_type_documents[$_operation->sejour_id][$_doc]["categorie_ids"] = array();
    $counter_type_documents[$_operation->sejour_id][$_doc]["files"]         = array();

    if ($categories_file = CAppUI::gconf("dPpmsi type_document $_doc")) {
      $counter_type_documents[$_operation->sejour_id][$_doc]["categorie_ids"] = explode("|", $categories_file);

      foreach ($operation_docs as $_operation_doc) {
        if (
            $_operation_doc->file_category_id
            && in_array(
                $_operation_doc->file_category_id,
                $counter_type_documents[$_operation->sejour_id][$_doc]["categorie_ids"]
            )
        ) {
          $counter_type_documents[$_operation->sejour_id][$_doc]["counter"]++;
          $counter_type_documents[$_operation->sejour_id][$_doc]["files"][$_operation_doc->_id] = $_operation_doc;
        }
      }

      foreach ($sejour_docs as $_sejour_doc) {
        if (
            $_sejour_doc->file_category_id
            && in_array(
                $_sejour_doc->file_category_id,
                $counter_type_documents[$_operation->sejour_id][$_doc]["categorie_ids"]
            )
        ) {
          $counter_type_documents[$_operation->sejour_id][$_doc]["counter"]++;
          $counter_type_documents[$_operation->sejour_id][$_doc]["files"][$_sejour_doc->_id] = $_sejour_doc;
        }
      }
    }
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("date_min"   , $date_min);
$smarty->assign("date_max"   , $date_max);
$smarty->assign("urgences" , $horsplages);
$smarty->assign('notReadFiles', CReadFile::getUnread($horsplages));
$smarty->assign("pageUrg", $page);
$smarty->assign("countUrg", $count);
$smarty->assign("step", $step);
$smarty->assign("counter_type_documents", $counter_type_documents);
$smarty->display("current_dossiers/inc_current_urgences");
