<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;

CCanDo::checkRead();
$consult_id = CValue::get("consult_id");

$consult = new CConsultation();
$consult->load($consult_id);

CAccessMedicalData::logAccess($consult);

$consult->loadRefPatient();
$consult->loadRefGrossesse();

$type = "";
switch ($consult->type_assurance) {
  case "classique" :
    $type = "assurance_classique";
    break;

  case "at" :
    $type = "accident_travail";
    break;

  case "smg" :
    $type = "soins_medicaux_gratuits";
    break;

  case "maternite" :
    $type = "maternite";
    break;
}

//smarty
$smarty = new CSmartyDP();
$smarty->assign("consult", $consult);
$smarty->display("inc_type_assurance_reglement/$type.tpl");