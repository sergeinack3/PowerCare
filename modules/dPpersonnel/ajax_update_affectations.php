<?php
/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Personnel\CAffectationPersonnel;

$step = CValue::get("step", 0);

$aff_pers = new CAffectationPersonnel();

$where                 = array();
$where["object_class"] = "= 'CPlageOp'";

$affs = $aff_pers->loadList($where, null, "$step,1000");

/**@var $_aff CAffectationPersonnel */
foreach ($affs as $_aff) {
  $plage = $_aff->loadRefObject();
  $ops   = $plage->loadBackIds("operations");

  foreach ($ops as $_op) {
    $affectation                       = new CAffectationPersonnel();
    $whereAff                          = array();
    $whereAff["personnel_id"]          = "= '$_aff->personnel_id'";
    $whereAff["object_class"]          = "= 'COperation'";
    $whereAff["object_id"]             = "= '$_op'";
    $whereAff["parent_affectation_id"] = "IS NULL";

    if ($affectation->loadObject($whereAff)) {
      $affectation->parent_affectation_id = $_aff->_id;
      $msg                                = $affectation->store();
      CAppUI::stepAjax($msg ? $msg : "Affectation modifiée", $msg ? UI_MSG_ERROR : UI_MSG_OK);
    }
  }
}

CAppUI::js('if (' . count($affs) . ' > 0) { 
  $V(getForm("Configure").step, parseInt($V(getForm("Configure").step))+1000);
  affUpdate();
 }');