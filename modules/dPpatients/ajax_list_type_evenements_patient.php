<?php
/**
 * @package Mediboard\dPpatients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CTypeEvenementPatient;

CCanDo::read();
$type_evenement_id = CView::get("type_evenement_id", "num");
CView::checkin();

$function    = new CFunctions();
$functions   = $function->loadListWithPerms(PERM_EDIT);
$inFunctions = CSQLDataSource::prepareIn(array_keys($functions));

$type  = new CTypeEvenementPatient();
$where = array();
if (CAppUI::$user->isAdmin()) {
  $where[] = "function_id IS NULL OR function_id " . $inFunctions;
}
else {
  $where["function_id"] = $inFunctions;
}
/** @var CTypeEvenementPatient[] $types */
$types = $type->loadList($where);
foreach ($types as $_type) {
  $_type->loadRefFunction();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("types", $types);
$smarty->assign("evenement_patient", new CEvenementPatient());
$smarty->assign("type_evenement_id", $type_evenement_id);

$smarty->display("inc_list_type_evenements_patient.tpl");
