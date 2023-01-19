<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$sejour_id = CValue::getOrSession("sejour_id");

$sejour = new CSejour;
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefDossierMedical()->updateFormFields();

$patient = $sejour->loadRefPatient();
$patient->loadRefDossierMedical()->updateFormFields();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("patient", $sejour->_ref_patient);
$smarty->assign("sejour" , $sejour);

$smarty->display("inc_list_diags");
