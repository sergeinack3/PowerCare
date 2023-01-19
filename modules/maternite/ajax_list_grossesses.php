<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CGrossesse;

/**
 * Liste des grossesses pour une parturiente
 */

CCanDo::checkRead();
$parturiente_id = CView::get("parturiente_id", "ref class|CPatient", true);
$object_guid    = CView::get("object_guid", "str", true);
$grossesse_id   = CView::get("grossesse_id", "ref class|CGrossesse");
$show_checkbox  = CView::get("show_checkbox", "bool");
CView::checkin();

$object = new CMbObject();
if ($object_guid) {
  $object = CMbObject::loadFromGuid($object_guid);
}

$grossesse                 = new CGrossesse();
$grossesse->parturiente_id = $parturiente_id;
$grossesses                = $grossesse->loadMatchingList("terme_prevu DESC, active DESC");

CStoredObject::massCountBackRefs($grossesses, "sejours");
CStoredObject::massCountBackRefs($grossesses, "consultations");
CStoredObject::massCountBackRefs($grossesses, "naissances");

$smarty = new CSmartyDP();
$smarty->assign("grossesses"    , $grossesses);
$smarty->assign("object"        , $object);
$smarty->assign("show_checkbox" , $show_checkbox);
$smarty->assign("parturiente_id", $parturiente_id);
$smarty->assign("grossesse_id"  , $grossesse_id);
$smarty->display("inc_list_grossesses");
