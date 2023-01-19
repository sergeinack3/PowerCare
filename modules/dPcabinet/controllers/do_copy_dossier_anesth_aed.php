<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultAnesth;

$consult_id = CValue::post("consult_id");

$dossier_anesth = new CConsultAnesth();
$dossier_anesth->consultation_id = $consult_id;

if ($msg = $dossier_anesth->store()) {
  CAppUI::setMsg($msg, UI_MSG_ERROR);
}
else {
  CAppUI::setMsg(CAppUI::tr("CConsultAnesth-msg-create"), UI_MSG_OK);
}

CAppUI::redirect($_POST["postRedirect"]);
