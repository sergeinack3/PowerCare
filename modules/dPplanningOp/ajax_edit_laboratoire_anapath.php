<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCando;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CLaboratoireAnapath;

CCanDo::checkAdmin();

$laboratoire_anapath_id = CView::get('laboratoire_anapath_id', 'ref class|CLaboratoireAnapath');

CView::checkin();

$laboratoire_anapath = CLaboratoireAnapath::findOrNew($laboratoire_anapath_id);

$smarty = new CSmartyDP();

$smarty->assign('laboratoire_anapath', $laboratoire_anapath);

$smarty->display('inc_edit_laboratoire_anapath');
