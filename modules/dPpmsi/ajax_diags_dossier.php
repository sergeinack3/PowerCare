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
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();
$sejour_id = CView::get("sejour_id", "ref class|CSejour");
$view_rhs  = CView::get("view_rhs", "bool default|0");
CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadExtDiagnostics();
$sejour->loadDiagnosticsAssocies();

$sejour->loadRelPatient();
$sejour->_ref_patient->loadRefDossierMedical();

$smarty = new CSmartyDP();
$smarty->assign("sejour"  , $sejour);
$smarty->assign("patient" , $sejour->_ref_patient);
$smarty->assign("view_rhs", $view_rhs);
$smarty->display("inc_diags_dossier");