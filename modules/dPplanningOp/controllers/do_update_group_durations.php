<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Mediboard\PlanningOp\CProtocole;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Core\CApp;
use Ox\Core\CSQLDataSource;
use Ox\Core\CAppUI;

$group                = CGroups::loadCurrent();
$protocole            = new CProtocole();
$function             = new CFunctions();
$user                 = new CMediusers();
$nb_protocols_updated = 0;

// On récupère les protocoles de l'établissement
$where           = [
  "group_id" => "= $group->_id",
];
$group_protocols = $protocole->loadList($where);

// On récupère les protocoles des fonctions de l'établissement
$functions_ids = $function->loadIds($where);

$whereIn = [
  "function_id" => CSQLDataSource::prepareIn($functions_ids)
];

$function_protocols = $protocole->loadList($whereIn);

// On récupère les protocoles des practiciens avec des fonctions sur l'établissement
$ljoin = [
  "functions_mediboard" => "users_mediboard.function_id = functions_mediboard.function_id"
];

$user_ids = $user->loadIds($where, null, null, null, $ljoin);

$whereIn         = [
  "chir_id" => CSQLDataSource::prepareIn($user_ids)
];
$users_protocols = $protocole->loadList($whereIn);

// On fusionne les tableaux pour obtenirs l'ensemble des protocoles de l'établissement en cours
$all_protocols = array_merge($group_protocols, $function_protocols, $users_protocols);

// Calcul des temps médians des protocoles
$all_protocols = CProtocole::computeMedian($all_protocols);

// Pour chaque protocole si temps median > temps operation on mets à jour
if (count($all_protocols)) {
  foreach ($all_protocols as $_protocol) {
    if (!$_protocol->_temps_median) {
      continue;
    }
    // Pour valuer $temp_operation, il faut valuer $_time_op (cf updateplainfields)
    $_protocol->_time_op = $_protocol->_temps_median;
    $nb_protocols_updated++;
    if ($msg = $_protocol->store()) {
      CAppUI::setMsg($msg, UI_MSG_WARNING);
    }
  }
}
CAppUI::stepAjax(CAppUI::tr('CProtocole-protocols_updated-%s', $nb_protocols_updated));
CApp::rip();
