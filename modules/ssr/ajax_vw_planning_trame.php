<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CPlageSeanceCollective;
use Ox\Mediboard\Ssr\CTrameSeanceCollective;
use Ox\Mediboard\System\CPlanningEvent;
use Ox\Mediboard\System\CPlanningWeek;

global $g, $m;
$trame_id      = CView::get("trame_id", "ref class|CTrameSeanceCollective", true);
$show_inactive = CView::get("show_plage_inactive", "bool default|0", true);
CView::checkin();

$trame = new CTrameSeanceCollective();
$trame->load($trame_id);
$function = $trame->loadRefFunction();

$monday                = CMbDT::date("monday this week");
$sunday                = CMbDT::date("sunday this week");
$planning              = new CPlanningWeek(CMbDT::date(), $monday, $sunday, 7, false, "auto", false, true);
$planning->title       = "Planning collectif - $trame->_view - " . $function->_view;
$planning->guid        = $trame->_guid;
$planning->hour_min    = "07";
$planning->hour_max    = "19";
$planning->pauses      = array("07", "12", "19");
$planning->no_dates    = true;
$planning->see_nb_week = false;

$days = array();
for ($i = $monday; $i <= $sunday; $i = CMbDT::date('+1 day', $i)) {
  $days[] = $i;
  $planning->addDayLabel($i, CMbString::capitalize(CMbDT::format($i, "%A")));
}

//Recherche des plages collectives
$where = array(
  "trame_id" => "= '$trame_id'",
);
if (!$show_inactive) {
  $where["active"] = "= '1'";
}
$plage  = new CPlageSeanceCollective();
$plages = $plage->loadList($where);

$use_acte_presta = CAppUI::gconf("ssr general use_acte_presta");
foreach ($plages as $_plage) {
  $debut = CMbDT::date("$_plage->day_week this week") . " " . $_plage->debut;
  $_plage->loadRefUser()->loadRefFunction();
  $_plage->loadRefsSejoursAffectes();
  $title = "";
  if ($_plage->nom) {
    $title = $_plage->nom . ": ";
  }
  $title .= $_plage->loadRefElementPrescription()->_view;
  $title .= " - " . $_plage->_ref_user->_view . "<br/> (" . count($_plage->_ref_sejours_affectes) . " " . CAppUI::tr("CPatient|pl") . ")";

  $css_classes = array();
  if (!$_plage->countBackRefs("actes_plage") && $use_acte_presta != "aucun") {
    $css_classes[] = "zero-actes";
  }

  //Ajout de l'évènement au planning
  $event              = new CPlanningEvent($_plage->_guid, $debut, $_plage->duree, $title, null, true, $css_classes);
  $event->type        = "rdvfull";
  $event->plage['id'] = 0;
  $event->color       = "#" . $_plage->_ref_user->_color;
  $event->important   = $_plage->active;
  $event->css_class   = $_plage->active ? "" : "hatching";
  $planning->addEvent($event);
}

// Création du template
$smarty = new CSmartyDP("modules/system");
$smarty->assign("planning", $planning);
$smarty->display("calendars/vw_week");
