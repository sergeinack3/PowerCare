<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CPreferences;

CCanDo::checkAdmin();

$key = CView::get("key", "str");

CView::checkin();

// Load preferences
$preference = new CPreferences();
$where = array(
  "key"   => "= '$key'",
  "value" => "IS NOT NULL"
);
$preferences = $preference->loadList($where);

// Mass preloading
/** @var CUser[] $users */
$users    = CStoredObject::massLoadFwdRef($preferences, "user_id");
$profiles = CStoredObject::massLoadFwdRef($users, "profile_id");

// Attach preferences to users
$default = null;
foreach ($preferences as $_preference) {
  if (!$_preference->user_id) {
    $default = $_preference;
    continue;
  }
  $users[$_preference->user_id]->_ref_preference = $_preference;
}

// Build profile hierarchy
$hierarchy = array(
  "default" => array()
);

foreach ($users as $_user) {
  if ($_user->profile_id && isset($users[$_user->profile_id])) {
    $hierarchy[$_user->profile_id][] = $_user->_id;
  }
  else {
    $hierarchy["default"][] = $_user->_id;
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("key"      , $key);
$smarty->assign("users"    , $users);
$smarty->assign("default"  , $default);
$smarty->assign("hierarchy", $hierarchy);

$smarty->display("report_prefs.tpl");
