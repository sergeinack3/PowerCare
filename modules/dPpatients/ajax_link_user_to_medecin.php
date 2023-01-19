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
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CMedecin;

CCanDo::checkEdit();

$user_id    = CView::get("user_id", "ref class|CMediusers");
$patient_id = CView::get("patient_id", "ref class|CPatient");

CView::checkin();

$user = CMediusers::get($user_id);

$medecin         = new CMedecin();
$medecin->nom    = $user->_user_last_name;
$medecin->prenom = $user->_user_first_name;
$medecin->actif  = "1";
if (CAppUI::isCabinet()) {
    $this->function_id = $user->function_id;
} elseif (CAppUI::isGroup()) {
    $this->group_id = $user->loadRefFunction()->group_id;
}
$medecins = $medecin->loadMatchingList();

$smarty = new CSmartyDP();

if (count($medecins) == 1) {
  $medecin          = reset($medecins);
  $medecin->user_id = $user->_id;
  $medecin->store();
  $medecin->updateFormFields();
  $smarty->assign("medecins", $medecin);
}
elseif (count($medecins)) {
  foreach ($medecins as $_medecin) {
    $_medecin->user_id = $user_id;
  }

  $smarty->assign("medecins", $medecins);
}
else {
  $medecin                  = new CMedecin();
  $medecin->nom             = $user->_user_last_name;
  $medecin->prenom          = $user->_user_first_name;
  $medecin->adresse         = $user->_user_adresse;
  $medecin->ville           = $user->_user_ville;
  $medecin->cp              = $user->_user_cp;
  $medecin->tel             = $user->_user_phone;
  $medecin->email           = $user->_user_email;
  $medecin->email_apicrypt  = $user->mail_apicrypt;
  $medecin->mssante_address = $user->mssante_address;
  $medecin->type            = "medecin";
  $medecin->adeli           = $user->adeli;
  $medecin->rpps            = $user->rpps;
  $medecin->user_id         = $user->_id;
  $msg                      = $medecin->store();
  $medecin->updateFormFields();
  $smarty->assign("medecins", $medecin);
}

$smarty->assign("patient_id", $patient_id);
$smarty->display("inc_link_user_to_medecin.tpl");
