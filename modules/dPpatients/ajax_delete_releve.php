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
$releve_id  = CView::get("releve_id", "ref class|CConstantReleve");
$patient_id = CView::get("patient_id", "ref class|CPatient");
$onlyActive = CView::get("onlyActive", "bool default|0");
CView::checkin();

$releve = new CConstantReleve();
$releve->load($releve_id);
if (!$releve->_id) {
  CAppUI::stepAjax(CAppUI::tr("CConstantReleve-msg-Not found"), UI_MSG_ERROR);
}

// suppression des constant value
try{
  $releve->storeInactive();
}
catch (CConstantException $exception) {
}

$releve = new CConstantReleve();
$releve->patient_id = $patient_id;
if ($onlyActive) {
  $releve->active = 1;
}
/** @var CConstantReleve[] $releves */
$releves = $releve->loadMatchingList();
CConstantReleve::massLoadAllConstantBackRefs($releves);

// Trie par la date de releve
CMbArray::ksortByProp($releves, "datetime");

$smarty = new CSmartyDP();
$smarty->assign("releves", $releves);
$smarty->display("inc_search_constantes.tpl");

