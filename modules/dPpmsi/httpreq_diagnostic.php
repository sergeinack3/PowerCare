<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;

$sejour_id = CValue::getOrSession("sejour_id");

$sejour = new CSejour;
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadExtDiagnostics();
$sejour->loadRefDossierMedical();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour" , $sejour);

$smarty->display("inc_diagnostic");
