<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkAdmin();

$user_class = CValue::post('user_class');
$tag        = CValue::post('tag');
$code       = CValue::post('code');
$data       = CValue::post('data');
$user_id    = CValue::post('user_id');
$create     = CValue::post('create');

//if ($create) {
//  /** @var CUser $user */
//  $user = ///
//  $mediuser = $user->loadRefMediuser();
//  $mediuser->actif = 0;
//  $mediuser->store();
//
//  CAppUI::js("createCallback('$code','$user->_id','".addslashes($user->_view)."')");
//  echo CAppUI::getMsg();
//  CApp::rip();
//}

if (!$user_id) {
  CAppUI::stepAjax('common-error-You have to select a user', UI_MSG_ERROR);
  return;
}

$user = $user_class::get($user_id);

$idex_object = CIdSante400::getMatch($user->_class, $tag, $code, $user->_id);

if (!$idex_object->_id) {
  if ($msg = $idex_object->store()) {
    CAppUI::stepAjax($msg, UI_MSG_WARNING);
  }
  else {
    CAppUI::stepAjax("Étiquette '$code' associée à l'utilisateur '$user'", UI_MSG_OK);
  }
}

CApp::rip();
