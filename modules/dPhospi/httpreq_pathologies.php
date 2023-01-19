<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\PlanningOp\CSejour;

$affichage_patho = CValue::getOrSession("affichage_patho");
$date            = CValue::getOrSession("date", CMbDT::date());
$pathos          = new CDiscipline();

// Recuperation de l'id du sejour
$sejour_id = CValue::get("sejour_id");

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefPraticien();
$sejour->_ref_praticien->loadRefFunction();
$sejour->loadRefPatient();

$sejour->loadRefsOperations();
foreach ($sejour->_ref_operations as &$operation) {
  $operation->loadExtCodesCCAM();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("pathos", $pathos);
$smarty->assign("date", $date);
$smarty->assign("curr_sejour", $sejour);
$smarty->assign("affichage_patho", $affichage_patho);
$smarty->display("inc_pathologies.tpl");

