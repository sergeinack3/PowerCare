<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\System\CPlanningEvent;
use Ox\Mediboard\System\CPlanningWeek;
use Ox\Mediboard\System\CPreferences;

CCanDo::checkRead();

$date_session = CAppUI::pref("suivisalleAutonome") ? false : true;
$date         = CView::get('date', 'date default|now', $date_session);
$blocs_ids    = CView::get('blocs_ids', 'str', true);
$salles_ids   = CView::get('salles_ids', 'str');
$hour_min     = CView::get('hour_min', 'num min|0 max|23 default|7');
$hour_max     = CView::get('hour_max', 'num min|0 max|23 default|20');

CView::checkin();

$salles_ids = $salles_ids != '' ? explode('|', $salles_ids) : array();

$group = CGroups::loadCurrent();

$blocs = array();
$salle = new CSalle();
/** @var CSalle[] $salles */
$salles = array();

/* Chargement des salles sélectionnées */
foreach ($blocs_ids as $bloc_id) {
  $bloc = CBlocOperatoire::loadFromGuid("CBlocOperatoire-$bloc_id");

  $where = array('bloc_id' => " = $bloc_id");

  if (count($salles_ids)) {
    $where['salle_id'] = CSQLDataSource::prepareIn($salles_ids);
  }

  $bloc->_ref_salles = $salle->loadListWithPerms(PERM_READ, $where, 'nom');
  $salles = array_merge($salles, $bloc->_ref_salles);
}

$planning = new CPlanningWeek(0, 0, count($salles), count($salles), false, 'auto');
$planning->guid = 'timeline_salle';

$hours = array();
for ($hour = $hour_min; $hour <= $hour_max; $hour++) {
  $hours[$hour] = str_pad($hour, 2, '0', STR_PAD_LEFT);
}

$planning->hours = $hours;
$planning->hour_min = str_pad($hour_min, 2, '0', STR_PAD_LEFT);
$planning->hour_max = str_pad($hour_max, 2, '0', STR_PAD_LEFT);
$planning->hour_divider = 12;
$planning->dragndrop = 0;
$planning->show_half = true;

$i = 0;
$today = CMbDT::date();
$time = CMbDT::time();
foreach ($salles as $salle) {
  /* Ajout de la salle dans le planning */
  $label = $salle->_shortview;
  if (count($blocs_ids) > 1) {
    $label = str_replace("-", "<br/>", $salle->_view);
  }

  $color = $salle->color ? "#{$salle->color}" : null;

  $planning->addDayLabel($i, $label, null, $color, null, true, array('salle_id' => $salle->_id));

  if ($date == $today) {
    $planning->addEvent(new CPlanningEvent(null, "$i $time", null, null, 'red', null, 'now'));
  }

  /** @var CSalle $salle */
  $salle->loadRefsForDay($date, false, true);
  $salle->_ref_lines_dm = array();

  foreach ($salle->_ref_plages as $plage) {
    $plage->_ref_lines_dm = array();

    addOperations($plage->_ref_operations, $planning, $i, $salle, $plage, 'ordered_operations');

    /* Ajout de la plage */
    $plage->loadRefChir()->loadRefFunction();
    $plage->loadRefAnesth()->loadRefFunction();
    $plage->loadRefSpec();

    $begin = "$i " . CMbDT::time(/*'-45 minutes', */$plage->debut);
    $duration = CMbDT::minutesRelative(CMbDT::time(/*'-45 minutes', */$plage->debut), CMbDT::time($plage->fin));

    $smarty = new CSmartyDP('modules/dPbloc');
    $smarty->assign('plage', $plage);
    $html = $smarty->fetch('timeline_salles/plageop_header.tpl');

    $event = new CPlanningEvent($plage->_guid, $begin, $duration, $html, "#eceff1", true, null, $plage->_guid, false);

    $event->below       = true;
    $event->type        = 'plage_planning';
    $event->plage["id"] = $plage->_id;

    if (CCanDo::edit()) {
      $event->addMenuItem('edit', 'Modifier cette plage');
      $event->addMenuItem('list', 'Gestion des interventions');
    }

    $planning->addEvent($event);
  }

  addOperations($salle->_ref_urgences, $planning, $i, $salle, null, 'emergencies');
  $i++;
}

$planning->rearrange(true);

$smarty = new CSmartyDP();
$smarty->assign('planning', $planning);
$smarty->display('inc_timeline_salles.tpl');


/**
 * Load the reference of the given operations
 *
 * @param COperation[]  $operations The operations
 * @param CPlanningWeek $planning   The planning week object
 * @param integer       $day        The day for which the event will be added
 * @param CSalle        $salle      The salle
 * @param CPlageOp      $plage      The plageop
 * @param string        $type       The type of operations (emergencies, ordered or unordered)
 *
 * @return void
 */
function addOperations($operations, $planning, $day, $salle, $plage, $type = null) {
  $systeme_materiel_expert = CAppUI::gconf("dPbloc CPlageOp systeme_materiel") == "expert";
  $dmi_active = CModule::getActive("dmi") && CAppUI::gconf("dmi CDM active");
  $multiple_label  = CAppUI::gconf("dPplanningOp COperation multiple_label");
  $user = CMediusers::get();
  $check_planning_visibility = false;
  if ($user->isChirurgien() || $user->isMedecin() || $user->isDentiste()) {
    $check_planning_visibility = true;
  }

  COperation::massCountActes($operations);

  if ($multiple_label) {
    CStoredObject::massLoadBackRefs($operations, "liaison_libelle", "numero");
  }

  CStoredObject::massLoadFwdRef($operations, 'type_anesth');

  foreach ($operations as $operation) {
    if ($operation->annulee && !CAppUI::pref('planning_bloc_show_cancelled_operations')) {
      continue;
    }

    /* Check the visibility conditions depending on the value of the surgeon's function permission,
       if the connected user is a also surgeon */
    if ($check_planning_visibility) {
      if ($plage && $plage->_id) {
        $chir = $plage->loadRefChir();
      }
      else {
        $chir = $operation->loadRefChir();
      }

      $permission = CPreferences::getPref('bloc_planning_visibility', $chir->_id);

      if (($permission['used'] == 'restricted' && $user->_id != $chir->_id)
          || ($permission['used'] == 'function' && $user->function_id != $chir->function_id)
      ) {
        continue;
      }
    }

    $operation->countActes();
    $operation->canDo();
    $operation->loadRefTypeAnesth();
    if ($multiple_label) {
      $operation->loadLiaisonLibelle();
    }

    if ($systeme_materiel_expert) {
      $besoins = $operation->loadRefsBesoins();
      CStoredObject::massLoadFwdRef($besoins, "type_ressource_id");
      foreach ($besoins as $_besoin) {
        $_besoin->loadRefTypeRessource();
      }
    }

    if ($dmi_active) {
      $salle->_ref_lines_dm = array_merge(
        $salle->_ref_lines_dm,
        $operation->_ref_sejour->loadRefPrescriptionSejour()->loadRefsLinesDM()
      );

      if ($type == 'ordered_operations') {
        $plage->_ref_lines_dm = array_merge(
          $plage->_ref_lines_dm,
          $operation->_ref_sejour->_ref_prescription_sejour->_ref_lines_dm
        );
      }
      elseif ($type == 'emergencies') {
        $salle->_ref_lines_dm_urgence = array_merge(
          $salle->_ref_lines_dm_urgence,
          $operation->_ref_sejour->_ref_prescription_sejour->_ref_lines_dm
        );
      }
    }

    /* Ajout de l'operation dans le planning */
    $color = CAppUI::isMediboardExtDark() ? '#444444' : 'lightgrey';
    $begin = $operation->time_operation;
    $end = CMbDT::addTime($operation->temp_operation, $begin);

    if ($operation->debut_op || $operation->entree_salle) {
      $begin = $operation->debut_op ? CMbDT::time($operation->debut_op) : CMbDT::time($operation->entree_salle);

      if ($operation->fin_op || $operation->sortie_salle) {
        $color = CAppUI::isMediboardExtDark() ? '#173517' : 'darkgreen';
        $end = $operation->fin_op ? CMbDT::time($operation->fin_op) : CMbDT::time($operation->sortie_salle);
      }
      else {
        $color = CAppUI::isMediboardExtDark() ? '#644117' : 'darkorange';
      }
    }
    elseif ($operation->fin_op || $operation->sortie_salle) {
      $color = CAppUI::isMediboardExtDark() ? '#644117' : 'darkorange';
      $end = $operation->fin_op ? CMbDT::time($operation->fin_op) : CMbDT::time($operation->sortie_salle);
    }

    $duration = CMbDT::minutesRelative($begin, $end);

    if ($plage && ($begin < $plage->debut || $end > $plage->fin)) {
      $color = CAppUI::isMediboardExtDark() ? '#4d2121' :'firebrick';
    }

    $smarty = new CSmartyDP('modules/dPbloc');
    $smarty->assign('operation', $operation);
    $smarty->assign('patient', $operation->_ref_patient);
    $html = $smarty->fetch('timeline_salles/operation_view.tpl');

    $event = new CPlanningEvent($operation->_guid, "$day $begin", $duration, $html, $color, true, null, $operation->_guid, false);

    if (CCanDo::edit()) {
      $event->addMenuItem('edit', 'Modifier cette intervention');
      $event->addMenuItem('print', 'Impression de la feuille de bloc');
    }

    $event->plage['id'] = $operation->_id;
    $event->type = 'tl_operation';

    $planning->addEvent($event);
  }
}
