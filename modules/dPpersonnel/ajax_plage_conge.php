<?php
/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

//CCanDo::checkRead();

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPlageConge;

$user_id  = CValue::getorSession("user_id");
$plage_id = CValue::get("plage_id");

$user = new CMediusers();
$user->load($user_id);

// Plages de congés pour l'utilisateur
$plage_conge          = new CPlageConge();
$plage_conge->user_id = $user_id;

$plages_conge = $plage_conge->loadMatchingList("date_debut");

foreach ($plages_conge as $_plage) {
  $_plage->loadFwdRef("replacer_id");
  $replacer =& $_plage->_fwd["replacer_id"];
  $replacer->loadRefFunction();
}

$new_plageconge = new CPlageConge();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("user", $user);
$smarty->assign("plages_conge", $plages_conge);
$smarty->assign("new_plageconge", $new_plageconge);
$smarty->assign("plage_id", $plage_id);
$smarty->display("inc_liste_plages_conge.tpl");
