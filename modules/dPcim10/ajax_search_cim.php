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
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Cim10\CFavoriCIM10;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$code           = CView::get('code', 'str');
$keywords       = CView::get('keywords', 'str');
$chapter        = CView::get('chapter', 'str');
$category       = CView::get('category', 'str');
$user_id        = CView::get('user_id', 'ref class|CMediusers');
$object_class   = CView::get('object_class', 'str');
$object_id      = CView::get('object_id', 'ref class|'.$object_class);
$sejour_type    = CView::get('sejour_type', 'enum list|mco|ssr|psy');
$field_type     = CView::get('field_type', 'enum list|dp|dr|da|fppec|mmp|ae|das');
$tag_id         = CView::get('tag_id', 'ref class|CTag');
$ged            = CView::get('ged', 'bool');

CView::checkin();

$user = CMediusers::get();
$user_profile = null;

if ($user_id) {
  $user_profile = CMediusers::get($user_id);
  $favoris = CFavoriCIM10::findCodes($user_profile, $code, $keywords, $chapter, $category, $tag_id, $sejour_type, $field_type);

  $used_codes = array();
  if ($user_profile->isPraticien()) {
    $used_codes = CCodeCIM10::getUsedCodesFor($user_profile, $code, $keywords, $chapter, $category, $sejour_type, $field_type);
  }

  $codes = array_merge($favoris, $used_codes);
}
else {
  $codes = CCodeCIM10::findCodes($code, $keywords, $chapter, $category, null, null, null, $sejour_type, $field_type);

  foreach ($codes as $code) {
    $code->isFavori($user);
  }
}

if ($object_class == "CConsultation") {
  $object = new CConsultation();
  $object->load($object_id);

  CAccessMedicalData::logAccess($object);
}

$smarty = new CSmartyDP();
if ($object_class == "CConsultation") {
  $smarty->assign('object', $object);
}

$smarty->assign('codes', $codes);
$smarty->assign('object_id', $object_id);
$smarty->assign('object_class', $object_class);
$smarty->assign('user', $user);
$smarty->assign('user_profile', $user_profile);
$smarty->assign('ged', $ged);
$smarty->display('cim/inc_search_results.tpl');
