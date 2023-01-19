<?php
/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Cim10\CFavoriCIM10;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$chir_id        = CView::get('chir_id', 'ref class|CMediusers');
$anesth_id      = CView::get('anesth_id', 'ref class|CMediusers');
$object_class   = CView::get('object_class', 'str');
$object_id      = CView::get('object_id', 'ref class|'.$object_class);
$sejour_type    = CView::get('sejour_type', 'enum list|mco|ssr|psy');
$field_type     = CView::get('field_type', 'enum list|dp|dr|da|fppec|mmp|ae|das');
$ged            = CView::get('ged', 'bool default|0');

CView::checkin();

$user = CMediusers::get();

$profiles = array(
  'user' => array(
    'user_id' => $user->_id,
    'user'    => $user,
    'tags'    => CFavoriCIM10::getTree($user->_id),
    'codes'   => array()
  )
);

if ($chir_id && $chir_id != $user->_id) {
  $chir = CMediusers::get($chir_id);
  $profiles['chir'] = array(
    'user_id' => $chir->_id,
    'user'    => $chir,
    'tags'    => CFavoriCIM10::getTree($chir->_id),
    'codes'   => array()
  );
}

if ($anesth_id && $anesth_id != $user->_id) {
  $anesth = CMediusers::get($anesth_id);
  $profiles['anesth'] = array(
    'user_id' => $anesth->_id,
    'user'    => $anesth,
    'tags'    => CFavoriCIM10::getTree($anesth->_id),
    'codes'   => array()
  );
}

foreach ($profiles as $type => $profile) {
  /** @var CMediusers $_user */
  $_user = $profile['user'];
  $favoris = CFavoriCIM10::findCodes($profile['user'], null, null, null, null, null, $sejour_type, $field_type);

  $used_codes = array();
  if ($_user->isPraticien()) {
    $used_codes = CCodeCIM10::getUsedCodesFor($_user, null, null, null, null, $sejour_type, $field_type);
  }

  $profiles[$type]['codes'] = array_merge($used_codes, $favoris);
}

$smarty = new CSmartyDP();
$smarty->assign('chir_id', $chir_id);
$smarty->assign('anesth_id', $anesth_id);
$smarty->assign('object_class', $object_class);
$smarty->assign('object_id', $object_id);
$smarty->assign('profiles', $profiles);
$smarty->assign('chapters', CCodeCIM10::getChapters());
$smarty->assign('codes', array());
$smarty->assign('user', $user);
$smarty->assign('sejour_type', $sejour_type);
$smarty->assign('field_type', $field_type);
$smarty->assign('ged', $ged);
$smarty->display('cim/inc_search.tpl');
