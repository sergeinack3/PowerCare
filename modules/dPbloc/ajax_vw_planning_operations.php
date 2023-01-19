<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\System\CPlanningEvent;
use Ox\Mediboard\System\CPlanningWeek;

CCanDo::checkEdit();

$salles_ids = CView::get("salles_ids", "str");
$date       = CView::get("date", "date");
$chir_id    = CView::get("chir_id", "num");

CView::checkin();

$plage = new CPlageOp();

$where = array(
  "plagesop.salle_id" => CSQLDataSource::prepareIn($salles_ids),
  "plagesop.date"     => "= '$date'",
  "plagesop.chir_id"  => "= '$chir_id'"
);

$ljoin = array(
  "sallesbloc" => "sallesbloc.salle_id = plagesop.salle_id"
);

$plages = $plage->loadList($where, "sallesbloc.nom", null, null, $ljoin);

$salles = CStoredObject::massLoadFwdRef($plages, "salle_id");

$plages_by_salle = array();

/** @var CPlageOp $_plage */
foreach ($plages as $_plage) {
  $plages_by_salle[$_plage->salle_id][$_plage->_id] = $_plage;
}

$hours_debut = CMbArray::pluck($plages, "debut");
$hours_fin   = CMbArray::pluck($plages, "fin");

foreach ($hours_debut as $key => $_hour) {
  $hours_debut[$key] = CMbDT::transform($_hour, null, "%H");
}

foreach ($hours_fin as $key => $_hour) {
  $hours_fin[$key] = CMbDT::transform($_hour, null, "%H");
}

$planning = new CPlanningWeek(0, 0, count($salles_ids), count($salles_ids), false, "auto");
$planning->title = "";

$planning->guid = "planning_interv";
$planning->hour_min = min($hours_debut);
$planning->hour_max = max($hours_fin);

$planning->hour_divider = 12;
$planning->show_half    = true;

CMbObject::massLoadRefsNotes($plages);
CStoredObject::massLoadFwdRef($plages, "chir_id");
CStoredObject::massLoadFwdRef($plages, "anesth_id");

$multi_salle_op = CAppUI::gconf("dPplanningOp COperation multi_salle_op");

$i = 0;
foreach ($salles as $_salle_id => $_salle) {
  $onclick = "MultiSalle.choosePlage(null, '$date', '$chir_id', null, '$_salle_id')";

  if (count($plages_by_salle[$_salle_id]) == 1) {
    $first_plage = reset($plages_by_salle[$_salle_id]);
    $onclick = "EditPlanning.edit('$first_plage->_id', '$first_plage->date')";
  }

  $planning->addDayLabel($i, $_salle->_view, null, null, $onclick);

  CStoredObject::massLoadBackRefs($plages_by_salle[$_salle_id], "operations", "rank, time_operation, rank_voulu, horaire_voulu");

  /** @var CPlageOp $_plage */
  foreach ($plages_by_salle[$_salle_id] as $_plage) {
    $_plage->loadRefSalle();

    $debut = "$i " . CMbDT::time($_plage->debut);
    $duree = CMbDT::minutesRelative(CMbDT::time($_plage->debut), CMbDT::time($_plage->fin));

    $_plage->loadRefsNotes();
    $_plage->loadRefChir()->loadRefFunction();
    $_plage->loadRefAnesth()->loadRefFunction();

    $event              = new CPlanningEvent($_plage->_guid, $debut, $duree, null, "#eceff1", true, null, null, false);
    $event->below       = true;
    $event->type        = "plage_planning";
    $event->plage["id"] = $_plage->_id;

    $planning->addEvent($event);

    $ops     = $_plage->loadRefsOperations(false, null, true, true);
    $sejours = CStoredObject::massLoadFwdRef($ops, "sejour_id");
    CStoredObject::massLoadFwdRef($sejours, "patient_id");
    CStoredObject::massLoadFwdRef($ops, "chir_id");
    $first_op = reset($ops);
    $last_op  = end($ops);

    $before_first_op = $after_last_op = new COperation();

    if ($multi_salle_op && count($ops)) {
      $seconde_plage = CPlageOp::findSecondePlageChir($_plage, $new_time);
      $new_time = $first_op->time_operation;
      $before_first_op = CPlageOp::findPrevOp($first_op, $_plage, $seconde_plage, $new_time);
      $new_time = $last_op->time_operation;
      $after_last_op   = CPlageOp::findNextOp($last_op, $_plage, $seconde_plage, $new_time);
    }

    foreach ($ops as $_op) {
      $_op->loadRefPlageOp();
      $_op->loadRefsConsultAnesth();
      $sejour  = $_op->loadRefSejour();
      $patient = $sejour->loadRefPatient();

      $horaire  = CMbDT::time($_op->_datetime_best);
      $debut    = "$i {$horaire}";
      $debut_op = $horaire;
      $fin_op   = $_op->sortie_salle ? CMbDT::time($_op->sortie_salle) : CMbDT::addTime($_op->temp_operation, $horaire);
      $duree    = CMbDT::minutesRelative($horaire, $fin_op);
      $color = CAppUI::gconf("dPhospi colors " . $sejour->type);

      $_op->loadRefChir()->loadRefFunction();
      $_op->loadRefChir2()->loadRefFunction();
      $_op->loadRefChir3()->loadRefFunction();
      $_op->loadRefChir4()->loadRefFunction();
      $_op->loadRefAnesth()->loadRefFunction();

      $sejour->loadRefCurrAffectation($_op->_datetime_best)->updateView();
      $sejour->loadRefChargePriceIndicator();

      $patient->loadRefDossierMedical()->countAntecedents();

      $smarty = new CSmartyDP("modules/reservation");

      $smarty->assign("operation", $_op);
      $smarty->assign("debut_op", CMbDT::time($_op->_datetime_best));
      $smarty->assign("fin_op", $_op->sortie_salle ? CMbDT::time($_op->sortie_salle) : CMbDT::addTime($_op->temp_operation, $horaire));

      $smarty_op = $smarty->fetch("inc_planning/libelle_plage.tpl");

      $event              = new CPlanningEvent($_op->_guid, $debut, $duree, $smarty_op, "#fff", true, null, $_op->_guid, false);
      $event->plage["id"] = $_op->_id;
      $event->type        = "operation_enplage";
      $event->right_toolbar = true;

      $event->addMenuItem("pause", "Modifier la pause");
      $event->addMenuItem("cancel", "Supprimer cette intervention");
      $event->addMenuItem("hslip", CAppUI::tr("move-salle"));

      if ($_op->_id != $first_op->_id || ($before_first_op && $before_first_op->_id)) {
        $event->addMenuItem("up", "Au dessus");
      }

      if ($_op->_id != $last_op->_id || ($after_last_op && $after_last_op->_id)) {
        $event->addMenuItem("down", "Au dessous");
      }

      $planning->addEvent($event);
    }
  }

  $i++;
}

$planning->rearrange(true);

$nb_hours = intval($planning->hour_max) - intval($planning->hour_min);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("planning", $planning);
$smarty->assign("date"    , $date);
$smarty->assign("chir_id" , $chir_id);
$smarty->assign("nb_hours", $nb_hours);

$smarty->display("inc_vw_planning_operations");
