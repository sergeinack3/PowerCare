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

$ampli_id = CView::get('ampli_id', 'ref class|CAmpli');

CView::checkin();

$ampli = CAmpli::findOrNew($ampli_id);

$smarty = new CSmartyDP();

$smarty->assign('ampli', $ampli);

$smarty->display('inc_edit_ampli');
