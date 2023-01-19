<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\Constants\CConstantReleve;

CCanDo::checkAdmin();
$releve_id  = CView::get("releve_id", "ref class|CConstantReleve");
$onlyActive = CView::get("onlyActive", "bool default|0");
if (!$releve_id) {
  CAppUI::stepAjax(CAppUI::tr("CConstantReleve-msg-Not found"), UI_MSG_ERROR);
}
CView::checkin();

$releve = new CConstantReleve();
$releve->load($releve_id);
if (!$releve->_id) {
  CAppUI::stepAjax(CAppUI::tr("CConstantReleve-msg-Not found"), UI_MSG_ERROR);
}

$where = array();
if ($onlyActive) {
  $where["active"] = "= '$onlyActive'";
}
$releve->loadAllValues($where);

$smarty = new CSmartyDP();
$smarty->assign("_releve", $releve);
$smarty->display("inc_search_releve_fieldset.tpl");
