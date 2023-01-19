<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::read();

$salle_id = CView::get("salle_id", "ref class|CSalle", true);
$date     = CView::get("date", "date default|now");

CView::checkin();

$listBlocs = CGroups::loadCurrent()->loadBlocs();
$listSalles = array();
foreach ($listBlocs as $_bloc) {
  $listSalles = $listSalles + $_bloc->loadRefsSalles();
}

if (!$salle_id) {
  $salle_id = reset($listSalles)->_id;
}

$salle = new CSalle();
$salle->load($salle_id);
$salle->loadRefBloc();

$bloc_id = $salle->bloc_id;

// Liste des jours
$listDays = array();
for ($i = 0; $i < 19*7; $i += 7) {
  $dateArr = CMbDT::date("+$i day", $date);
  $listDays[$dateArr] = $dateArr;
}

$listPlages         = array();
$operation          = new COperation();
$nbIntervHorsPlage  = 0;
$listPlage          = new CPlageOp();
$nbIntervNonPlacees = 0;

// Création du tableau de visualisation
$affichages = array();
foreach ($listDays as $keyDate=>$valDate) {
  $salle->_blocage[$valDate] = $salle->loadRefsBlocages($valDate);
  foreach (CPlageOp::$hours as $keyHours=>$valHours) {
    foreach (CPlageOp::$minutes as $keyMins=>$valMins) {
      // Initialisation du tableau
      $affichages[$bloc_id]["$keyDate-$valHours:$valMins:00"] = "empty";
      $affichages[$bloc_id]["$keyDate-HorsPlage"] = array();
    }
  }
}

foreach ($listDays as $keyDate => $valDate) {
  // Récupération des plages par jour
  $where = array();
  $where["date"]     = "= '$keyDate'";
  $where["salle_id"] = "= '$salle->_id'";
  $order             = "debut";
  $listPlages[$keyDate][$bloc_id] = $listPlage->loadList($where, $order);
  
  // Récupération des interventions hors plages du jour
  $where = array();
  $where["date"]      = "= '$keyDate'";
  $where["annulee"]   = "= '0'";
  $where["salle_id"]  = "= '$salle->_id'";
  $where["plageop_id"] = "IS NULL";
  $order = "time_operation";
  /** @var COperation[] $horsPlages */
  $horsPlages = $operation->loadList($where, $order);
  
  // Détermination des bornes du semainier
  $min = CPlageOp::$hours_start.":".reset(CPlageOp::$minutes).":00";
  $max = CPlageOp::$hours_stop.":".end(CPlageOp::$minutes).":00";

  CStoredObject::massLoadFwdRef($listPlages[$keyDate][$bloc_id], "chir_id");
  CStoredObject::massLoadFwdRef($listPlages[$keyDate][$bloc_id], "anesth_id");
  CStoredObject::massLoadFwdRef($listPlages[$keyDate][$bloc_id], "spec_id");
  CStoredObject::massLoadFwdRef($listPlages[$keyDate][$bloc_id], "salle_id");
  CStoredObject::massLoadBackRefs($listPlages[$keyDate][$bloc_id], "affectations_personnel");
  CMbObject::massLoadRefsNotes($listPlages[$keyDate][$bloc_id]);

  // Détermination des bornes de chaque plage
  foreach ($listPlages[$keyDate][$bloc_id] as $plage) {
    /** @var CPlageOp $plage */
    $plage->loadRefChir()->loadRefFunction();
    $plage->loadRefOriginalChir()->loadRefFunction();
    $plage->loadRefSpec();
    $plage->loadRefOriginalSpec();
    $plage->loadRefAnesth();
    $plage->loadRefSalle();
    $plage->makeView();
    $plage->loadRefsNotes();
    $plage->multicountOperations();
    $nbIntervNonPlacees += $plage->_count_operations - $plage->_count_operations_placees;
    $plage->loadAffectationsPersonnel();
  
    $plage->fin = min($plage->fin, $max);
    $plage->debut = max($plage->debut, $min);

    if ($plage->debut >= $plage->fin) {
      unset($listPlages[$keyDate][$bloc_id][$plage->_id]);
    }
  }
  
  // Remplissage du tableau de visualisation
  foreach ($listPlages[$keyDate][$bloc_id] as $plage) {
    $plage->debut = CMbDT::timeGetNearestMinsWithInterval($plage->debut, CPlageOp::$minutes_interval);
    $plage->fin   = CMbDT::timeGetNearestMinsWithInterval($plage->fin  , CPlageOp::$minutes_interval);
    $plage->_nbQuartHeure = CMbDT::timeCountIntervals($plage->debut, $plage->fin, "00:".CPlageOp::$minutes_interval.":00");
    for ($time = $plage->debut; $time < $plage->fin; $time = CMbDT::time("+".CPlageOp::$minutes_interval." minutes", $time) ) {
      $affichages[$bloc_id]["$keyDate-$time"] = "full";
    } 
    $affichages[$bloc_id]["$keyDate-$plage->debut"] = $plage->_id;
  }

  // Ajout des interventions hors plage
  foreach ($horsPlages as $_op) {
    if ($_op->salle_id) {
      $affichages[$bloc_id]["$keyDate-HorsPlage"][$_op->_id] = $_op;
    }
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("listPlages"        , $listPlages        );
$smarty->assign("listDays"          , $listDays          );
$smarty->assign("listBlocs"         , $listBlocs         );
$smarty->assign("salle"             , $salle             );
$smarty->assign("listHours"         , CPlageOp::$hours   );
$smarty->assign("listMins"          , CPlageOp::$minutes );
$smarty->assign("affichages"        , $affichages        );
$smarty->assign("date"              , $date              );
$smarty->assign("key_bloc"          , $bloc_id           );

$smarty->display("monitor_day_salle.tpl");