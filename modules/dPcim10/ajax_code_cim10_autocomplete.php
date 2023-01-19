<?php
/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Cim10\CFavoriCIM10;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::check();

$limit_favoris       = CView::request('limit_favoris', 'bool default|0');
$chir_id             = CView::request('chir_id', 'ref class|CMediusers');
$keywords            = CView::request("keywords_code", 'str');
$sejour_type         = CView::get('sejour_type', 'enum list|mco|ssr|psy');
$field_type          = CView::get('field_type', 'enum list|dp|dr|da|fppec|mmp|ae|das');
$sejour_id           = CView::get("sejour_id", 'ref class|CSejour');

CView::checkin();
CView::enforceSlave();

if ($limit_favoris) {
  $users = array(CMediusers::get());
  if ($chir_id) {
    $users[] = CMediusers::get($chir_id);
  }
  $codes  = CFavoriCIM10::findCodes($users, $keywords, null, null, null, null, $sejour_type, $field_type);
}
else {
  $codes = CCodeCIM10::findCodes($keywords, $keywords, null, null, null, null, null, $sejour_type, $field_type, CMediusers::get());
}

$smarty = new CSmartyDP();

$smarty->assign("codes"    , $codes);
$smarty->assign("nodebug"  , true);
$smarty->assign("keywords" , $keywords);
$smarty->assign("sejour_id", $sejour_id);

$smarty->display("inc_code_cim10_autocomplete.tpl");
