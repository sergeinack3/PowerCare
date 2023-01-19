<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$object_class = CView::get("object_class", "str");
$object_id    = CView::get("object_id", "ref class|$object_class");
$object_guid  = CView::get("object_guid", "str default|" . "$object_class-$object_id");

/* @var CSejour $sejour */
$sejour = CMbObject::loadFromGuid($object_guid);

if (!$sejour || !$sejour->_id) {
    CAppUI::notFound($object_guid);
}

CView::checkin();

$sejour->loadRefsMacrocible();

$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("readonly", true);
$smarty->assign("show_compact_trans", true);
$smarty->assign("list_transmissions", $sejour->_ref_macrocibles);

$smarty->display("inc_list_transmissions.tpl");
