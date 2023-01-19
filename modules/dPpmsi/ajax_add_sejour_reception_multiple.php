<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$nda = CView::get('nda', 'str');
CView::checkin();

$sejour = new CSejour();
if ($nda) {
  $sejour->loadFromNDA($nda);
}

$sejour->loadRefPatient();
$sejour->loadRefPraticien();
$sejour->_ref_praticien->loadRefFunction();
$sejour->loadRefsOperations(array("operations.annulee" => "= '0'"));
$smarty = new CSmartyDP();
$smarty->assign('sejour', $sejour);
$smarty->display('reception_multiple/inc_line_sejour');