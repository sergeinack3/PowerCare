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

CCanDo::checkRead();
$code          = CView::get('code', 'str');
$keywords      = CView::get('keywords', 'str');
$hierarchy_1   = CView::get('hierarchy_1', 'str');
$hierarchy_2   = CView::get('hierarchy_2', 'str');
$hierarchy_3   = CView::get('hierarchy_3', 'str');
$object_class  = CView::get('object_class', 'str');
$object_id     = CView::get('object_id', "ref class|$object_class");
$hide_selector = CView::get('hide_selector', "bool default|0");
CView::checkin();

$user = CMediusers::get();

$hierarchy = null;
if ($hierarchy_3) {
  $hierarchy = $hierarchy_3;
}
elseif ($hierarchy_2) {
  $hierarchy = $hierarchy_2;
}
elseif ($hierarchy_1) {
  $hierarchy = $hierarchy_1;
}

$codes = CActiviteCsARR::findCodes($keywords, $code, $hierarchy, null, 0, 30);

foreach ($codes as $code) {
  $code->isFavori($user);
}

$smarty = new CSmartyDP();
$smarty->assign('user'         , $user);
$smarty->assign('codes'        , $codes);
$smarty->assign('object_id'    , $object_id);
$smarty->assign('object_class' , $object_class);
$smarty->assign('hide_selector', $hide_selector);
$smarty->display('csarr/inc_search_results');