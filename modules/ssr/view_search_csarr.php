<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Ssr\CActiviteCsARR;
use Ox\Mediboard\Ssr\CFavoriCsARR;
use Ox\Mediboard\Ssr\CHierarchieCsARR;

CCanDo::checkRead();
$user_id      = CView::get('chir_id', 'ref class|CMediusers');
$object_class = CView::get('object_class', 'str');
$object_id    = CView::get('object_id', 'ref class|' . $object_class);
CView::checkin();

$user = CMediusers::get();

$profiles = array(
  'user' => array(
    'user_id' => $user->_id,
    'user'    => $user,
    'codes'   => array()
  )
);

if ($user_id && $user_id != $user->_id) {
  $praticien             = CMediusers::get($user_id);
  $profiles['praticien'] = array(
    'user_id' => $praticien->_id,
    'user'    => $praticien,
    'codes'   => array()
  );
}

foreach ($profiles as $type => $profile) {
  /** @var CMediusers $_user */
  $_user   = $profile['user'];
  $favoris = CFavoriCsARR::findCodes($profile['user']);

  $used_codes = array();
  if ($_user->isProfessionnelDeSante()) {
    $used_codes = CActiviteCsARR::getUsedCodesFor($_user);
  }

  $profiles[$type]['codes'] = array_merge($used_codes, $favoris);
}

$smarty = new CSmartyDP();
$smarty->assign('user_id'     , $user_id);
$smarty->assign('object_class', $object_class);
$smarty->assign('object_id'   , $object_id);
$smarty->assign('profiles'    , $profiles);
$smarty->assign('user'        , $user);
$smarty->assign('chapters'    , CHierarchieCsARR::getChapters());
$smarty->assign('codes'       , array());
$smarty->display('csarr/inc_search');