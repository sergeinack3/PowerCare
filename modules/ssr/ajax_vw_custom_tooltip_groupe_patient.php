<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CPlageGroupePatient;

global $m;

CCanDo::check();

$object_class = CView::get("object_class", "str");
$object_id    = CView::get("object_id", "ref class|$object_class");
$object_guid  = CView::get("object_guid", "str default|" . "$object_class-$object_id");

/** @var CPlageGroupePatient $object */
$object = CMbObject::loadFromGuid($object_guid);

if (!$object || !$object->_id) {
    CAppUI::notFound($object_guid);
}
$date = CView::get("day_used", "date default|now", true);
CView::checkin();

$object->_date = CMbDT::date("$object->groupe_day this week", $date);

$object->loadView();

$smarty = new CSmartyDP();
$smarty->assign("object", $object);
$smarty->assign("is_plage_groupe", true);
$smarty->display("CPlageGroupePatient_view");
