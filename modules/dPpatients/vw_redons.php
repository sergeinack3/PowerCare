<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CRedon;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$sejour_id = CView::get("sejour_id", "ref class|CSejour");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

$sejour->loadRefPatient();
$sejour->loadPatientBanner();

$redon = new CRedon();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);

$smarty->display("vw_redons");
