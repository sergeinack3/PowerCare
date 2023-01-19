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
use Ox\Mediboard\Ssr\CHierarchieCsARR;

CCanDo::checkRead();

$code  = CView::get('code', 'str notNull');
$level = CView::get('level', 'num');

CView::checkin();

$parent = CHierarchieCsARR::get($code);
$parent->loadRefsChildHierarchies();

$smarty = new CSmartyDP();
$smarty->assign('parent', $parent);
$smarty->assign('level', $level);
$smarty->display('csarr/inc_filter_hierarchy');