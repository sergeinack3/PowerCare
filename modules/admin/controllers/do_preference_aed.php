<?php

/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CacheManager;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\System\CPreferences;

$prefs      = CValue::post("pref", array());
$user_id    = CValue::post("user_id");
$restricted = CValue::post("restricted");

foreach ($prefs as $key => $value) {
  CPreferences::setPref($key, $user_id, $value, $restricted, false);
}
if ($user_id) {
  CAppUI::buildPrefs();
}

if ($redirect = CValue::post("postRedirect")) {
  echo $redirect;
  CAppUI::redirect($redirect);
}
else {
  echo CAppUI::getMsg();
  CApp::rip();
}
