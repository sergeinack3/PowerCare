<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cim10\Atih\CCodeCIM10ATIH;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$sejour_id = CView::get("sejour_id", "ref class|CSejour");
$modal     = CView::get("modal", "bool default|0");
$rss_id    = CView::get("rss_id", "ref class|CRSS");
$version   = CView::get("version", "str default|". CAppUI::conf('cim10 cim10_version'));
CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadExtDiagnostics();
$sejour->loadDiagnosticsAssocies();

$codes_dp = array();
$codes_dr = array();

if ($version == 'atih') {
  $codes_dp = CCodeCIM10ATIH::getForbiddenCodes('mco', 'dp');
  $codes_dr = CCodeCIM10ATIH::getForbiddenCodes('mco', 'dr');
}

$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("modal" , $modal);
$smarty->assign("rss_id", $rss_id);
$smarty->assign("codes_dp" , $codes_dp);
$smarty->assign("codes_dr" , $codes_dr);

$smarty->display("inc_diags_pmsi");