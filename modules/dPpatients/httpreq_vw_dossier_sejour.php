<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;

$sejour_id = CValue::get("sejour_id");

// Chargement du sejour
$sejour = new CSejour();
$sejour->load($sejour_id);
CAccessMedicalData::checkForSejour($sejour);
$sejour->loadComplete();

$sejour->canDo();

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign("object", $sejour);
$smarty->assign("isImedsInstalled", (CModule::getActive("dPImeds") && CImeds::getTagCIDC(CGroups::loadCurrent())));
$smarty->display('inc_vw_dossier_sejour.tpl'); 
