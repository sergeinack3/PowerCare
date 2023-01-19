<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$param_guid = CView::get("param_guid", "str");

CView::checkin();

$param = CStoredObject::loadFromGuid($param_guid);

if (!$param->_id) {
  $param->coding_system = "MB";
}
else {
  $param->loadRefsNotes();
  $param->countUsages();
}

$group  = new CGroups();
$groups = $group->loadList();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("param", $param);
$smarty->assign("groups", $groups);
$smarty->display("inc_edit_config_param_surveillance");
