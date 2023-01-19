<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Admin\CBrisDeGlace;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::check();

$object_class = CView::get("object_class", "str");
$object_id    = CView::get("object_id", "ref class|$object_class");
$object_guid  = CView::get("object_guid", "str default|" . "$object_class-$object_id");

$object = CMbObject::loadFromGuid($object_guid);

if (!$object || !$object->_id) {
    CAppUI::notFound($object_guid);
}
$not_printable = CView::get("not-printable", "bool default|0");

CView::checkin();
CView::enableSlave();

if (
    $object instanceof CSejour && CBrisDeGlace::isBrisDeGlaceRequired()
    && !CAccessMedicalData::checkForSejour($object)
) {
    CAppUI::accessDenied();
}

$object->loadComplete();

$object->needsRead();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("canSante400", CModule::getCanDo("dPsante400"));
$smarty->assign("object", $object);
$smarty->assign("not_printable", $not_printable);

$template = $object->makeTemplatePath("complete");

$smarty->display(__DIR__ . "/../$template");
