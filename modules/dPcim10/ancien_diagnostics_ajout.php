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
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$object_guid = CView::get("object_guid", "str notNull");
CView::checkin();

$object = CMbObject::loadFromGuid($object_guid);

if ($object instanceof CSejour) {
  $template_name = "anciens_diagnostics_line";

  $object->loadExtDiagnostics();
  $object->loadRefDossierMedical();
  $object->loadDiagnosticsAssocies();
}
else {
  $template_name = "anciens_diagnostics_rhs_line";

  $object->loadRefDiagnostics();
}

$smarty = new CSmartyDP();

$smarty->assign("object", $object);
$smarty->display($template_name);
