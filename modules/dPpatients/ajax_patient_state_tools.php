<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatientStateTools;

CCanDo::checkAdmin();

$action = CValue::get("action");
$state  = CValue::get("state");

switch ($action) {
  case "verifyStatus":
    $result = CPatientStateTools::verifyStatus();
    CAppUI::stepAjax("Il y a $result patients n'ayant pas de statut");
    break;
  case "createStatus":
    $result = CPatientStateTools::createStatus($state);
    CAppUI::stepAjax("Il y a $result patients dont le status a été créés");
    break;
  default:
    CAppUI::stepAjax("Action non spécifiée");
}

CAppUI::getMsg();
