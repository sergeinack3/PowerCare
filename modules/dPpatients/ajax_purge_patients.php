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
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkAdmin();

$patient = new CPatient();

// Supression de patients
$suppr        = 0;
$error        = 0;
$qte          = CValue::get("qte", 1);
$listPatients = $patient->loadList(null, null, $qte);

foreach ($listPatients as $_patient) {
  CAppUI::setMsg($_patient->_view, UI_MSG_OK);
  if ($msg = $_patient->purge()) {
    CAppUI::setMsg($msg, UI_MSG_ALERT);
    $error++;
    continue;
  }
  CAppUI::setMsg("patient supprimé", UI_MSG_OK);
  $suppr++;
}

// Nombre de patients
$nb_patients = $patient->countList();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("resultsMsg", CAppUI::getMsg());
$smarty->assign("suppr", $suppr);
$smarty->assign("error", $error);
$smarty->assign("nb_patients", $nb_patients);

$smarty->display("inc_purge_patients.tpl");

