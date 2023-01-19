<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CPreferences;

CCanDo::check();

$key   = CValue::post('key');
$value = CValue::post('value');

if (!$key) {
  CAppUI::commonError();
}

CPreferences::setPref($key, CUser::get()->_id, stripcslashes($value));

echo CAppUI::getMsg();

CApp::rip();