<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;

$callback = CValue::post("callback");
$group    = CGroups::loadCurrent();

$patient            = new CPatient;
$patient->nom       = "anonyme";
$patient->prenom    = "anonyme";
$patient->tutelle   = "aucune";
$patient->sexe      = CAppUI::conf("dPpatients CPatient anonymous_sexe", $group);
$patient->naissance = CAppUI::conf("dPpatients CPatient anonymous_naissance", $group);

$msg = $patient->store();

if ($msg) {
  CAppUI::setMsg($msg, UI_MSG_ERROR);
}
else {
  CAppUI::setMsg(CAppUI::tr("CPatient-msg-create"), UI_MSG_OK);
}

echo CAppUI::getMsg();

if ($callback) {
  CAppUI::callbackAjax($callback, $patient->_id, $patient->getProperties());
}

CApp::rip();