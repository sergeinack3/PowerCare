<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\Forms\CExClass;

CCanDo::checkEdit();

CView::checkin();

$in_hermetic_mode = CExClass::inHermeticMode(false);

$smarty = new CSmartyDP();
$smarty->assign('in_hermetic_mode', $in_hermetic_mode);
$smarty->display("vw_import_ex_class.tpl");
