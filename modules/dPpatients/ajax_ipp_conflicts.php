<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkAdmin();

$idex               = new CIdSante400();
$idex->object_class = "CPatient";
$idex->tag          = CAppUI::conf("dPpatients CPatient tag_conflict_ipp") . CAppUI::conf("dPpatients CPatient tag_ipp");
/** @var CIdSante400[] $ipp_conflicts */
$ipp_conflicts = $idex->loadMatchingList();

$conflicts = array();
foreach ($ipp_conflicts as $_conflict) {
  $patient_conflict = new CPatient();
  $patient_conflict->load($_conflict->object_id);
  $patient_conflict->loadIPP();

  $IPP               = new CIdSante400();
  $IPP->object_class = "CPatient";
  $IPP->tag          = CAppUI::conf("dPpatients CPatient tag_ipp");
  $IPP->id400        = $_conflict->id400;
  $IPP->loadMatchingObject();

  $patient = new CPatient();
  $patient->load($IPP->object_id);

  $patient->loadIPP();

  $conflicts[] = array(
    "patient"          => $patient,
    "patient_conflict" => $patient_conflict
  );
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("conflicts", $conflicts);

$smarty->display("inc_ipp_conflicts.tpl");