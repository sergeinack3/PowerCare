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
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$code  = CView::get('code', 'str notNull');
$type  = CView::get('type', 'enum list|code|chapter default|code');
$modal = CView::get('modal', 'bool default|0');

CView::checkin();

$code = CCodeCIM10::get($code, CCodeCIM10::FULL);

$user = CMediusers::get();
$code->isFavori($user);

$smarty = new CSmartyDP();
$smarty->assign('code', $code);
$smarty->assign('user', $user);
$smarty->assign('version', CCodeCIM10::getVersion());
$smarty->assign('modal', $modal);
$smarty->display('cim/inc_details.tpl');

