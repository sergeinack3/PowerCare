<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCando;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CLaboratoireBacterio;

CCanDo::checkAdmin();

$laboratoire_bacterio_id = CView::get('laboratoire_bacterio_id', 'ref class|CLaboratoireBacterio');

CView::checkin();

$laboratoire_bacterio = CLaboratoireBacterio::findOrNew($laboratoire_bacterio_id);

$smarty = new CSmartyDP();

$smarty->assign('laboratoire_bacterio', $laboratoire_bacterio);

$smarty->display('inc_edit_laboratoire_bacterio');
