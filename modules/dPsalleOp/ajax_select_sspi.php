<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;

CCanDo::checkEdit();

$bloc_id = CView::get("bloc_id", "ref class|CBlocOperatoire");

CView::checkin();

$bloc = new CBlocOperatoire();
$bloc->load($bloc_id);

$sspis = $bloc->loadRefsSSPIs();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sspis", $sspis);

$smarty->display("inc_select_sspi");