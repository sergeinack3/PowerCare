<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultation;

$consultation_id = CValue::post("consultation_id");
$plageconsult_id = CValue::post("plageconsult_id");
$heure           = CValue::post("heure");

$consult = new CConsultation();
$consult->load($consultation_id);
$consult->_id = "";
$consult->_hour = $consult->_min = null;
$consult->plageconsult_id = $plageconsult_id;
$consult->heure = $heure;
$consult->chrono = CConsultation::PLANIFIE;

$msg = $consult->store();

if ($msg) {
  CAppUI::setMsg($msg, UI_MSG_ERROR);
}
else {
  CAppUI::setMsg(CAppUI::tr("CConsultation-msg-create"), UI_MSG_OK);
}

echo CAppUI::getMsg();

CApp::rip();
