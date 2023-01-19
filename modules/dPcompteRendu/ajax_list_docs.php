<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$object_class = CView::get("object_class", "str");
$object_id    = CView::get("object_id", "num");

CView::checkin();

$object = CStoredObject::loadFromGuid("$object_class-$object_id");

/** @var CPatient $patient */
if ($object instanceof CPatient) {
  $patient = $object;
}
else {
  $patient = $object->loadRelPatient();
}

$patient->loadRefsDocs();

foreach ($patient->loadRefsConsultations() as $_consult) {
  $_consult->loadRefPlageConsult();
  $_consult->loadRefsDocs();
}

foreach ($patient->loadRefsSejours() as $_sejour) {
  $_sejour->loadRefsDocs();

  foreach ($_sejour->loadRefsOperations() as $_operation) {
    $_operation->loadRefsDocs();
  }
}

$smarty = new CSmartyDP();

$smarty->assign("patient", $patient);

$smarty->display("inc_list_docs");