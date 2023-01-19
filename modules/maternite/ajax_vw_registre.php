<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Maternite\CNaissance;

CCanDo::checkRead();

$page     = CView::get("page", "num default|0");
$date_min = CView::get("_date_min", "date", true);
$date_max = CView::get("_date_max", "date", true);
CView::checkin();

$group = CGroups::loadCurrent();

$naissance = new CNaissance();

$ljoin = array(
  "sejour"   => "sejour.sejour_id = naissance.sejour_enfant_id",
  "patients" => "patients.patient_id = sejour.patient_id"
);

$where = array(
  "sejour.group_id" => "= '$group->_id'",
);

$where["patients.naissance"] = "BETWEEN '$date_min' AND '$date_max'";

$count = $naissance->countList($where, null, $ljoin);

$naissances = $naissance->loadList($where, "num_naissance, patients.nom", "$page,50", null, $ljoin);

$sejours_enfants  = CStoredObject::massLoadFwdRef($naissances, "sejour_enfant_id");
$patients_enfants = CStoredObject::massLoadFwdRef($sejours_enfants, "patient_id");
CStoredObject::massLoadBackRefs($patients_enfants, "constantes", "datetime ASC");

$sejours_mamans = CStoredObject::massLoadFwdRef($naissances, "sejour_maman_id");
CStoredObject::massLoadFwdRef($sejours_mamans, "patient_id");

/** @var CNaissance $_naissance */
foreach ($naissances as $_naissance) {
  $_naissance->loadRefSejourEnfant()->loadRefPatient()->getFirstConstantes();
  $_naissance->loadRefSejourMaman()->loadRefPatient();
}

$smarty = new CSmartyDP();

$smarty->assign("naissances", $naissances);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("count", $count);
$smarty->assign("page", $page);

$smarty->display("inc_vw_registre");