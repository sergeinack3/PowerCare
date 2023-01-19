<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Récupérations des paramètres
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CObservationMedicale;
use Ox\Mediboard\Mediusers\CMediusers;

$sejours_id           = CValue::post("sejours_ids");
$sejours_effectue_ids = CValue::post("sejours_effectue_ids");
$sejours_id           = json_decode(utf8_encode(stripslashes($sejours_id)), true);
$sejours_effectue_ids = json_decode(utf8_encode(stripslashes($sejours_effectue_ids)), true);

$count_no_valide = 0;
$user            = CMediusers::get();

if ($sejours_id) {
  foreach ($sejours_id as $_sejour_id => $_sejour) {
    $checked = $_sejour["_checked"];
    if ($checked) {
      $observation            = new CObservationMedicale();
      $observation->sejour_id = $_sejour_id;
      $observation->user_id   = $user->_id;
      $observation->degre     = "info";
      $observation->date      = CMbDT::dateTime();
      $observation->text      = "Visite effectuée";
      $msg                    = $observation->store();
      CAppUI::displayMsg($msg, "CObservationMedicale-msg-create");
    }
    else {
      $count_no_valide++;
    }
  }
}

if ($sejours_effectue_ids) {
  foreach ($sejours_effectue_ids as $_sejour_id => $_sejour) {
    $checked = $_sejour["_checked"];
    if ($checked) {
      $observation            = new CObservationMedicale();
      $observation->sejour_id = $_sejour_id;
      $observation->user_id   = $user->_id;
      $observation->degre     = "info";
      $observation->date      = CMbDT::dateTime();
      $observation->text      = "Visite effectuée";
      $msg                    = $observation->store();
      CAppUI::displayMsg($msg, "CObservationMedicale-msg-create");
    }
  }
}
CAppUI::js(" $('visites_jour_prat').title = '$count_no_valide visite(s) non effectuée(s)';");
echo CAppUI::getMsg();
CApp::rip();