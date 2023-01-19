<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\Constants\CConstantException;
use Ox\Mediboard\Patients\Constants\CConstantReleve;

CCanDo::checkAdmin();
$patient_id = CView::get("patient_id", "ref class|CPatient");
$onlyActive = CView::get("onlyActive", "bool default|0");
$check      = CView::get("check"     , "bool");
CView::checkin();

if (!$patient_id) {
  CAppUI::stepAjax(CAppUI::tr("CConstantReleve-msg-Patient not found"), UI_MSG_ERROR);
}
$releve = new CConstantReleve();
$releve->patient_id = $patient_id;
$where = array();
if ($onlyActive) {
  $releve->active = 1;
  $where = array("active" =>"= '1'");
}
$order = "datetime DESC";
$limits = "0,150";
/** @var CConstantReleve[] $releves */
$releves = $releve->loadMatchingList($order, $limits);

CConstantReleve::massLoadAllConstantBackRefs($releves, $where);

if ($check) {
  foreach ($releves as $key => $_releve) {
    if (!$_releve->checkActive()) {
      try {
        $_releve->storeInactive(false);
        unset($releves[$key]);
      } catch (CConstantException $constantException) {
      }
    }
  }
}
if (count($releves) == 0) {
  CAppUI::stepAjax(CAppUI::tr("CConstantReleve-msg-Constantes any medical statement"), UI_MSG_ALERT);
  return;
}
// Trie par la date de releve
CMbArray::ksortByProp($releves, "datetime");

$smarty = new CSmartyDP();
$smarty->assign("releves", $releves);
$smarty->assign("patient_id", $patient_id);
$smarty->display("inc_search_constantes.tpl");

