<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cim10\Atih\CCodeCIM10ATIH;
use Ox\Mediboard\PlanningOp\CSejour;

$sejour_id  = CView::get("sejour_id", "ref class|CSejour", true);
$consult_id = CView::get("consult_id", "ref class|CConsultation", true);
$version    = CView::get("version", "str default|". CAppUI::conf('cim10 cim10_version'));

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadExtDiagnostics();
$sejour->loadRefDossierMedical();
$sejour->loadDiagnosticsAssocies();

$codes_dp = array();

if ($version == 'atih') {
  $codes_dp = CCodeCIM10ATIH::getForbiddenCodes('mco', 'dp');
}

$consult = new CConsultation();
$consult->load($consult_id);

CAccessMedicalData::logAccess($consult);

$consult->countActes();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("consult", $consult);
$smarty->assign("codes_dp", $codes_dp);

$smarty->display("inc_diagnostic_principal.tpl");
