<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CTransmissionMedicale;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Prescription\CCategoryPrescription;

CModule::getCanDo("soins")->needsEdit();

$sejour_id     = CView::get("sejour_id", "ref class|CSejour");
$macrocible_id = CView::get("macrocible_id", "ref class|CCategoryPrescription");
$cible_id      = CView::get("cible_id", "ref class|CCible");
$focus_area    = CView::get("focus_area", "enum list|data|action|result");
CView::checkIn();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

$categorie_prescription = new CCategoryPrescription();
$where                  = array(
  "cible_importante" => "= '1'",
  "inactive"         => "= '0'",
  "group_id"         => "IS NULL OR group_id = '" . CGroups::loadCurrent()->_id . "'"
);
$macrocibles            = $categorie_prescription->loadList($where, "nom");

$transmission               = new CTransmissionMedicale();
$transmission->sejour_id    = $sejour_id;
$transmission->cible_id     = $cible_id;
$transmission->user_id      = CMediusers::get()->_id;
$transmission->type         = "data";
$transmission->object_class = "CCategoryPrescription";
$transmission->object_id    = $macrocible_id ? $macrocible_id : reset($macrocibles)->_id;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("transmission", $transmission);
$smarty->assign("macrocibles", $macrocibles);
$smarty->assign("macrocible_id", $macrocible_id);
$smarty->assign("data_id", "");
$smarty->assign("action_id", "");
$smarty->assign("result_id", "");
$smarty->assign("date", CMbDT::date());
$smarty->assign("hour", CMbDT::format(CMbDT::time(), "%H"));
$smarty->assign("focus_area", $focus_area);

$smarty->display("inc_macrocible.tpl");
