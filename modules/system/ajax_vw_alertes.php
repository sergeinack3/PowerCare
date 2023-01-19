<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Prescription\CPrescription;

CCanDo::check();

/**
 * Show alerts not handled
 */
$object_guid   = CView::get("object_guid", "str");
$level         = CView::get("level", "str");
$tag           = CView::get("tag", "str");

if (!$tag) {
  $tag = null;
}

CView::checkin();

$object = CMbObject::loadFromGuid($object_guid);

if (!$object->_guid) {
  CApp::rip();
}

$object->loadAlertsNotHandled($level, $tag, null);
$object->canDo();
$object->needsRead();

$alert_ids = CMbArray::pluck($object->_refs_alerts_not_handled, "_id");

// Pour le traitement des alertes de prescription, check de la permission fonctionnelle
if ($object instanceof CPrescription) {
  $ampoule_see_action = CAppUI::pref("ampoule_see_action");
  $curr_user = CMediusers::get();

  switch ($ampoule_see_action) {
    default:
      break;
    case "4":
      $alert_ids = array();
      foreach ($object->_refs_alerts_not_handled as $_alert) {
        if ($_alert->loadRefUser()->function_id === $curr_user->function_id) {
          $alert_ids[] = $_alert->_id;
        }
      }
      break;
    case "5":
      $alert_ids = array();
      foreach ($object->_refs_alerts_not_handled as $_alert) {
        if ($_alert->_edit_access) {
          $alert_ids[] = $_alert->_id;
        }
      }
  }

  $object->_ref_object->loadRefPatient();
}

$smarty = new CSmartyDP();

$smarty->assign("object"       , $object);
$smarty->assign("level"        , $level);
$smarty->assign("alert_ids"    , $alert_ids);

$smarty->display("inc_vw_alertes");
