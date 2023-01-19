<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCando;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();

$_date = CView::get('_date', array('dateTime', 'default' => CMbDT::dateTime("-7 day")));
CView::checkin();

$smarty = new CSmartyDP();
$smarty->assign("_date", $_date);
$smarty->display("inc_tools_purge.tpl");
