<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\Purge\CObjectPurger;

CCanDo::checkAdmin();

$class_name = CView::get("class_name", "str notNull");

CView::checkin();

CView::enforceSlave();

$class_name = strstr($class_name, '-purge', true);

$purger = CObjectPurger::getPurger($class_name);

$count = $purger->countPurgeable();

$smarty = new CSmartyDP();
$smarty->assign("class_name", $class_name);
$smarty->assign("count", $count);
$smarty->display("inc_purge_class");