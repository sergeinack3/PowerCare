<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;

$object_guid = CValue::get("object_guid");

// Chargement du patient
/** @var CPatient $patient */
$patient = CMbObject::loadFromGuid($object_guid);

// Chargement de son dossier médical
$patient->loadRefDossierMedical();
$dossier_medical =& $patient->_ref_dossier_medical;

$all_absence = array();

// Chargement des allergies   
$allergies = array();
if ($dossier_medical->_id) {
  $all_absence = $dossier_medical->loadRefsAllergies(true);
  $dossier_medical->loadRefsAllergies();
  $allergies = $dossier_medical->_ref_allergies;
}

$keywords = explode("|", CAppUI::gconf("soins Other ignore_allergies"));

foreach ($keywords as $_keyword) {
  foreach ($allergies as $_key => $_allergie) {
    if (preg_match('/^' . strtolower($_keyword) . '$/', strtolower($_allergie->_view))) {
      unset($allergies[$_key]);
      break;
    }
  }
}

$count_abs_allergie = count($all_absence);

$smarty = new CSmartyDP();
$smarty->assign("allergies"         , $allergies);
$smarty->assign("all_absence"       , $all_absence);
$smarty->assign("count_abs_allergie", $count_abs_allergie);
$smarty->display("inc_vw_allergies.tpl");
