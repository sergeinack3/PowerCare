<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Personnel\CPlageConge;
use Ox\Mediboard\Ssr\CEvenementSSR;

$sejour_id   = CView::post("sejour_id", "ref class|CSejour");
$conge_id    = CView::post("conge_id", "str");
$replacer_id = CView::post("replacer_id", "ref class|CMediusers");
$date        = CView::get("date", "date default|now", true);
CView::checkin();

// Standard plage
$conge = new CPlageConge();
$conge->load($conge_id);

// Week dates
$monday = CMbDT::date("last monday", CMbDT::date("+1 DAY", $date));
$sunday = CMbDT::date("next sunday", CMbDT::date("-1 DAY", $date));

// Pseudo plage for user activity
if (preg_match("/[deb|fin][\W][\d]+/", $conge_id)) {
  list($activite, $user_id) = explode("-", $conge_id);
  $limit = $activite == "deb" ? $monday : $sunday;
  $conge = CPlageConge::makePseudoPlage($user_id, $activite, $limit);
}

// Events to be transfered
$evenement              = new CEvenementSSR();
$where                  = array();
$date_min               = max($monday, CMbDT::date($conge->date_debut));
$date_max               = CMbDT::date("+1 DAY", min($sunday, CMbDT::date($conge->date_fin)));
$where["therapeute_id"] = " = '$conge->user_id'";
$where["sejour_id"]     = " = '$sejour_id'";
$where["debut"]         = " BETWEEN '$date_min' AND '$date_max'";

/** @var CEvenementSSR[] $evenements */
$evenements = $evenement->loadList($where);

$ljoin                                           = array(
  "evenement_ssr evt_child ON evt_child.seance_collective_id = evenement_ssr.evenement_ssr_id"
);
$where_collective                                = array();
$where_collective["evt_child.sejour_id"]         = " = '$sejour_id'";
$where_collective["evt_child.type_seance"]       = " = 'collective'";
$where_collective["evenement_ssr.therapeute_id"] = " = '$conge->user_id'";
$where_collective["evenement_ssr.debut"]         = " BETWEEN '$date_min' AND '$date_max'";
$evenements_collectifs                           = $evenement->loadList($where_collective, null, null, "evenement_ssr.evenement_ssr_id", $ljoin);

$evenements = array_merge($evenements, $evenements_collectifs);
foreach ($evenements as $_evenement) {
  $_evenement->therapeute_id = $replacer_id;
  $msg                       = $_evenement->store();
  CAppUI::displayMsg($msg, "CEvenementSSR-msg-modify");
}

echo CAppUI::getMsg();
CApp::rip();
