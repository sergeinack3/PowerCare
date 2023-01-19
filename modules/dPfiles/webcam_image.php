<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;

CCanDo::checkRead();

$object_guid = CView::get("object_guid", "str");
$rename      = CView::get("rename", "str");
CView::checkin();

$object = CStoredObject::loadFromGuid($object_guid);

$smarty = new CSmartyDP();

$smarty->assign("object", $object);
$smarty->assign("rename", $rename);

$smarty->display("webcam_image");