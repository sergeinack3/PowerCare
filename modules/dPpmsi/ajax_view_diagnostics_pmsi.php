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

// Chargement du séjour
$sejour  = new CSejour();
$sejour->load(CValue::get("sejour_id"));

CAccessMedicalData::logAccess($sejour);

$sejour->canDo();
$sejour->loadRefsOperations();
$sejour->loadRefsConsultAnesth();
$sejour->loadRefDossierMedical();

// Chargement du dossier du patient
$patient = $sejour->loadRefPatient();
$patient->loadRefDossierMedical();


// Création du template
$smarty = new CSmartyDP();

$smarty->assign("patient"         , $patient);
$smarty->assign("sejour"          , $sejour);

$smarty->display("inc_vw_diagnostics_pmsi");
