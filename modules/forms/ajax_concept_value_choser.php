<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\FieldSpecs\CEnumSpec;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExConcept;

if (CExClass::inHermeticMode(false)) {
    CCanDo::checkAdmin();
} else {
    CCanDo::checkEdit();
}

$concept_id = CValue::get("concept_id");

$concept = new CExConcept();
$concept->load($concept_id);
$concept->loadView();

$list_owner = $concept->getRealListOwner();
$list_owner->loadView();
$list_owner->loadRefItems();

$spec = CExConcept::getConceptSpec($concept->prop);
if ($spec instanceof CEnumSpec) {
  $list_owner->updateEnumSpec($spec);
}

$smarty = new CSmartyDP();
$smarty->assign("concept", $concept);
$smarty->assign("spec", $spec);
$smarty->display("inc_concept_value_choser.tpl");

