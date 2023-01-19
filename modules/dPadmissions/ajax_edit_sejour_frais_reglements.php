<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour_id = CView::get('sejour_id', 'ref class|CSejour notNull', true);

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);
$sejour->loadRefLastOperation(true);

$smarty = new CSmartyDP();
$smarty->assign('sejour', $sejour);
$smarty->display('inc_edit_reglement_frais_sejour.tpl');
