<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkAdmin();

$action = CValue::post("action", "modify");

$patient = new CPatient;

switch($action) {
  case "modify":
    while(!$patient->load(rand(1, 5000)));
    
    // randomize name
    $nom = str_split($patient->nom);
    shuffle($nom);
    $patient->nom = implode("", $nom);
  break;
  
  case "create":
    $patient->sample();
    //$patient->updateFormFields();
  break;
}

CAppUI::displayMsg($patient->store(), "CPatient-msg-$action");

echo CAppUI::getMsg();

CApp::rip();
