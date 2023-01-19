<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CInfoType;
use Ox\Mediboard\Mediusers\CMediusers;

$type_id = CView::get('info_type_id', 'ref class|CInfoType');

CView::checkin();

$type = new CInfoType();

if ($type_id) {
  $type->load($type_id);
}
else {
  $user          = CMediusers::get();
  $type->user_id = $user->_id;
}

$smarty = new CSmartyDP();
$smarty->assign('type', $type);
$smarty->display('inc_edit_info_type.tpl');