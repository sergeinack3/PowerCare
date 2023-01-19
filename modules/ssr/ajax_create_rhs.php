<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CRHS;

CCanDo::checkRead();
$sejour_id   = CView::get("sejour_id", "ref class|CSejour");
$date_monday = CView::get("date_monday", "date");
CView::checkin();

// Séjour concernés
$sejour = new CSejour;
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$last_rhs = $sejour->loadRefLastRhs();

$rhs            = new CRHS();
$rhs->sejour_id = $sejour->_id;
if ($last_rhs) {
  $rhs->FPP = $last_rhs->FPP;
  $rhs->MMP = $last_rhs->MMP;
  $rhs->AE  = $last_rhs->AE;
  $rhs->DAS = $last_rhs->DAS;
  $rhs->DAD = $last_rhs->DAD;
}
$rhs->date_monday  = $date_monday;
$rhs->_date_sunday = CMbDT::date("+6 DAY", $date_monday);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("sejour", $sejour);
$smarty->assign("rhs", $rhs);
$smarty->assign("last_rhs", $last_rhs);
$smarty->display("inc_create_rhs");
