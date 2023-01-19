<?php
/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CRHS;

CCanDo::checkRead();

$object_guid = CView::get("object_guid", "str notNull");
CView::checkin();

$object = CMbObject::loadFromGuid($object_guid);

if ($object instanceof CSejour) {
  $template_name = "anciens_diagnostics";

  $object->loadExtDiagnostics();
  $object->loadRefDossierMedical();
  $object->loadDiagnosticsAssocies();
  $patient = $object->loadRefPatient();

  $where = array(
    "sejour_id" => " <> '$object->_id'",
  );
  $objects = $patient->loadRefsSejours($where);
  CStoredObject::massLoadBackRefs($objects, "dossier_medical");
  foreach ($objects as $_sejour) {
    $_sejour->loadExtDiagnostics();
    $_sejour->loadDiagnosticsAssocies();
  }
}
else {
  $template_name = "anciens_diagnostics_rhs";

  $object->loadRefDiagnostics();
  $sejour = $object->loadRefSejour();
  $sejour->loadRefPatient();

  $objects = CRHS::getAllRHSsFor($sejour);
  foreach ($objects as $_key => $_rhs) {
    if (!$_rhs->_id || $_rhs->_id == $object->_id) {
      unset($objects[$_key]);
      continue;
    }
    $_rhs->loadRefDiagnostics();
  }
}

$smarty = new CSmartyDP();

$smarty->assign("object", $object);
$smarty->assign("objects", $objects);

$smarty->display($template_name);
