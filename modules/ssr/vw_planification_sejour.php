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
use Ox\Mediboard\Ssr\CBilanSSR;

CCanDo::checkRead();
global $m, $current_m;

if (!isset($current_m)) {
  $current_m = CView::setSession("current_m", $m);
}

$sejour_id = CView::get("sejour_id", "ref class|CSejour", true);
CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$patient = $sejour->loadRefPatient();
$sejour->loadNDA();
$patient->loadIPP();

// Bilan SSR
$bilan            = new CBilanSSR();
$bilan->sejour_id = $sejour->_id;
$bilan->loadMatchingObject();

// Création du template
$smarty = new CSmartyDP("modules/ssr");

$smarty->assign("today", CMbDT::date());
$smarty->assign("sejour", $sejour);
$smarty->assign("patient", $patient);
$smarty->assign("current_m", $current_m);
$smarty->assign("bilan", $bilan);
$smarty->assign("in_modal", 1);

$smarty->display("inc_planification");
