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

CView::checkIn();

$laboratoire_apanath = new CLaboratoireAnapath();
$laboratoires_apanath = $laboratoire_apanath->loadGroupList(null, 'libelle');

$smarty = new CSmartyDP();

$smarty->assign('laboratoires_anapath', $laboratoires_apanath);

$smarty->display('inc_list_laboratoires_anapath');
