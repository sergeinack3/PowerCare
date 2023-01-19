<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CPlageRessourceCab;
use Ox\Mediboard\Cabinet\CRessourceCab;
use Ox\Mediboard\System\CPlanningEvent;
use Ox\Mediboard\System\CPlanningRange;
use Ox\Mediboard\System\CPlanningWeek;

CCanDo::checkEdit();

$ressource_cab_id = CView::get("ressource_cab_id", "ref class|CRessourceCab", true);
$date             = CView::get("date", "date default|now", true);

CView::checkin();

$ressource = new CRessourceCab();
$ressource->load($ressource_cab_id);

$plage = new CPlageRessourceCab();

// Période
$debut = CMbDT::date("last sunday", $date);
$fin   = CMbDT::date("next sunday", $debut);
$debut = CMbDT::date("+1 day", $debut);

$dateArr = CMbDT::date("+6 day", $debut);
$nbDays = 7;

$where = array(
  "date"             => "= '$dateArr'",
  "ressource_cab_id" => "= '$ressource_cab_id'"
);

if (!$plage->countList($where)) {
  $nbDays--;
  // Aucune plage le dimanche, on peut donc tester le samedi.
  $dateArr = CMbDT::date("+5 day", $debut);
  $where["date"] = "= '$dateArr'";
  if (!$plage->countList($where)) {
    $nbDays--;
  }
}

$planning = new CPlanningWeek($debut, $debut, $fin, $nbDays, false, "auto");
$planning->guid = $ressource_cab_id;
$planning->hour_min = "07";
$planning->hour_max = "20";
$planning->title = $ressource->_view;
$planning->hour_divider = 60 / CAppUI::gconf("dPcabinet CPlageconsult minutes_interval");
$planning->no_dates = 0;
$planning->reduce_empty_lines = 1;

$where = array(
  "ressource_cab_id" => "= '$ressource_cab_id'",
  "date"             => "BETWEEN '$debut' AND '$fin'"
);

/** @var CPlageRessourceCab $_plage */
foreach ($plage->loadList($where) as $_plage) {
  $jour = $_plage->date . " " . $_plage->debut;
  $range = new CPlanningRange(
    $_plage->_guid,
    $jour,
    CMbDT::minutesRelative($_plage->debut, $_plage->fin),
    $_plage->libelle,
    $_plage->color
  );
  $range->type = "plageressource";
  $planning->addRange($range);

  $_plage->loadRefsReservations();

  foreach ($_plage->getUtilisation() as $_time => $_nb) {
    if (!$_nb) {
      $debute = "$_plage->date $_time";
      $event = new CPlanningEvent($debute, $debute, $_plage->_freq, "", $_plage->color, true, "droppable", null);
      $event->type = "rdvfree";
      $event->plage["id"] = $_plage->_id;
      $event->plage["color"] = $_plage->color;
      //Ajout de l'évènement au planning
      $planning->addEvent($event);
    }
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("planning"        , $planning);
$smarty->assign("debut"           , $debut);
$smarty->assign("fin"             , $fin);
$smarty->assign("ressource_cab_id", $ressource_cab_id);
$smarty->assign("height_calendar" , CAppUI::pref("height_calendar", "2000"));

$smarty->display("inc_vw_planning_ressources.tpl");