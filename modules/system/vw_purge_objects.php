<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CUserLog;

CCanDo::checkAdmin();

CView::checkin();

$classes = CApp::getChildClasses(CMbObject::class, false, true);

$smarty = new CSmartyDP();

$smarty->assign("user_log", new CUserLog());
$smarty->assign("classes" , $classes);

$smarty->display("vw_purge_objects.tpl");