<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCando;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CAmpli;

CCanDo::checkAdmin();

CView::checkIn();

$ampli = new CAmpli();
$amplis = $ampli->loadGroupList(null, 'libelle');

$smarty = new CSmartyDP();

$smarty->assign('amplis', $amplis);

$smarty->display('inc_list_amplis');
