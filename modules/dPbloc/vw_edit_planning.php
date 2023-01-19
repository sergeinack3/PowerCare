<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

$listBlocs = CGroups::loadCurrent()->loadBlocs(PERM_READ, true, "nom", array("actif" => "= '1'"));

$blocs_ids          = CView::get("blocs_ids", "str", true);
$date               = CView::get("date", "date default|now", true);
$type_view_planning = CView::get("type_view_planning", "str default|day", true);
CView::checkin();

if (count($listBlocs) && !is_array($blocs_ids)) {
  $blocs_ids   = array();
  $blocs_ids[] = reset($listBlocs)->_id;
}

if ($type_view_planning == "day") {
  $debut = $fin = $date;
}
else {
  //sunday = first day of week ...
  if (date("w", strtotime($date)) == 0) {
    $date = CMbDT::date("-1 DAY", $date);
  }
  $debut = CMbDT::date("this week", $date);
  $fin   = CMbDT::date("next sunday", $debut);
}

$bloc  = new CBlocOperatoire();
$whereBloc                       = array();
$whereBloc["bloc_operatoire_id"] = CSQLDataSource::prepareIn($blocs_ids);
$whereBloc["actif"]              = " = '1'";
$blocs = $bloc->loadList($whereBloc);

// Liste des jours
$listDays = array();
$nbIntervByDay = array();
for ($i = $debut; $i <= $fin; $i = CMbDT::date("+1 day", $i)) {
  $listDays[$i] = $i;
  foreach ($blocs as $bloc) {
    $nbIntervByDay[$i][$bloc->_id] = 0;
  }
}

$listPlages         = array();
$operation          = new COperation();
$nbIntervHorsPlage  = 0;
$listPlage          = new CPlageOp();
$nbIntervNonPlacees = 0;
$countOp            = 0;

$nbAlertesInterv = 0;

$salles = array();
CStoredObject::massLoadBackRefs($blocs, "salles", "nom", array("actif" => "= '1'"));

foreach ($blocs as $bloc) {
  $bloc->canDo();
  $bloc->loadRefsSalles(array("actif" => "= '1'"));
  $nbAlertesInterv += count($bloc->loadRefsAlertesIntervs());

  $salles[$bloc->_id] = $bloc->_ref_salles;
}

// Création du tableau de visualisation
$affichages = array();
foreach ($listDays as $keyDate => $valDate) {
  foreach ($blocs as $bloc) {
    foreach ($salles[$bloc->_id] as $keySalle => $valSalle) {
      $valSalle->_blocage[$valDate] = $valSalle->loadRefsBlocages($valDate);
      foreach (CPlageOp::$hours as $keyHours => $valHours) {
        foreach (CPlageOp::$minutes as $keyMins => $valMins) {
          // Initialisation du tableau
          $affichages[$bloc->_id]["$keyDate-s$keySalle-$valHours:$valMins:00"] = "empty";
          $affichages[$bloc->_id]["$keyDate-s$keySalle-HorsPlage"]             = array();
        }
      }
    }
  }
}

// Nombre d'interventions hors plage pour la semaine
foreach ($blocs as $bloc) {
  $ljoin                    = array();
  $ljoin["sejour"]          = "sejour.sejour_id = operations.sejour_id";
  $where                    = array();
  $where["date"]            = "BETWEEN '$debut' AND '$fin'";
  $where["plageop_id"]      = "IS NULL";
  $where["annulee"]         = "= '0'";
  $where[]                  = "salle_id IS NULL OR salle_id " . CSQLDataSource::prepareIn(array_keys($salles[$bloc->_id]));
  $where["sejour.group_id"] = "= '" . CGroups::loadCurrent()->_id . "'";
  $nbIntervHorsPlage        += $operation->countList($where, null, $ljoin);
}

foreach ($listDays as $keyDate => $valDate) {
  foreach ($blocs as $bloc) {
    // Récupération des plages par jour
    $where                            = array();
    $where["date"]                    = "= '$keyDate'";
    $where["salle_id"]                = CSQLDataSource::prepareIn(array_keys($salles[$bloc->_id]));
    $order                            = "debut";
    $listPlages[$keyDate][$bloc->_id] = $listPlage->loadList($where, $order);

    // Récupération des interventions hors plages du jour
    $where                  = array();
    $where["date"]          = "= '$keyDate'";
    $where["annulee"]       = "= '0'";
    $where["plageop_id"]    = "IS NULL";
    $where["salle_id"]      = CSQLDataSource::prepareIn(array_keys($salles[$bloc->_id]));
    $order                  = "time_operation";
    $horsPlages[$bloc->_id] = $operation->loadList($where, $order);

    // Détermination des bornes du semainier
    $min = CPlageOp::$hours_start . ":" . reset(CPlageOp::$minutes) . ":00";
    $max = CPlageOp::$hours_stop . ":" . end(CPlageOp::$minutes) . ":00";

    /**
     * @var CplageOp $plage
     */
    // Détermination des bornes de chaque plage
    foreach ($listPlages[$keyDate][$bloc->_id] as $plage) {
      $plage->loadRefsFwd();
      $plage->loadRefsNotes();
      $plage->_ref_chir->loadRefsFwd();
      $plage->multicountOperations();
      $nbIntervNonPlacees                  += $plage->_count_operations - $plage->_count_operations_placees;
      $countOp                             += $plage->_count_operations;
      $nbIntervByDay[$keyDate][$bloc->_id] = $countOp;
      $plage->loadAffectationsPersonnel();

      $plage->fin   = min($plage->fin, $max);
      $plage->debut = max($plage->debut, $min);

      $plage->updateFormFields();
      $plage->makeView();
      $plage->loadRefOriginalOwner();
      $plage->loadRefSecondaryFunction();

      if ($plage->debut >= $plage->fin) {
        unset($listPlages[$keyDate][$bloc->_id][$plage->_id]);
      }
    }

    //reset compteur opération
    $countOp = 0;

    // Remplissage du tableau de visualisation
    foreach ($listPlages[$keyDate][$bloc->_id] as $plage) {
      $plage->debut         = CMbDT::timeGetNearestMinsWithInterval($plage->debut, CPlageOp::$minutes_interval);
      $plage->fin           = CMbDT::timeGetNearestMinsWithInterval($plage->fin, CPlageOp::$minutes_interval);
      $plage->_nbQuartHeure = CMbDT::timeCountIntervals($plage->debut, $plage->fin, "00:" . CPlageOp::$minutes_interval . ":00");
      for ($time = $plage->debut; $time < $plage->fin; $time = CMbDT::time("+" . CPlageOp::$minutes_interval . " minutes", $time)) {
        $affichages[$bloc->_id]["$keyDate-s$plage->salle_id-$time"] = "full";
      }
      $affichages[$bloc->_id]["$keyDate-s$plage->salle_id-$plage->debut"] = $plage->_id;
    }
    // Ajout des interventions hors plage
    /**
     * @var COperation $_op
     */
    foreach ($horsPlages[$bloc->_id] as $_op) {
      if ($_op->salle_id) {
        $affichages[$bloc->_id]["$keyDate-s" . $_op->salle_id . "-HorsPlage"][$_op->_id] = $_op;
      }
    }
  }
}

// Liste des Spécialités
$listSpec = new CFunctions();
$listSpec = $listSpec->loadSpecialites();

//Création du template
$smarty = new CSmartyDP();
$smarty->assign("listPlages"            , $listPlages);
$smarty->assign("listDays"              , $listDays);
$smarty->assign("listBlocs"             , $listBlocs);
$smarty->assign("blocs_ids"             , $blocs_ids);
$smarty->assign("bloc"                  , $bloc);
$smarty->assign("blocs"                 , $blocs);
$smarty->assign("salles"                , $salles);
$smarty->assign("listHours"             , CPlageOp::$hours);
$smarty->assign("listMins"              , CPlageOp::$minutes);
$smarty->assign("type_view_planning"    , $type_view_planning);
$smarty->assign("affichages"            , $affichages);
$smarty->assign("nbIntervNonPlacees"    , $nbIntervNonPlacees);
$smarty->assign("nbIntervByDay"         , $nbIntervByDay);
$smarty->assign("nbIntervHorsPlage"     , $nbIntervHorsPlage);
$smarty->assign("nbAlertesInterv"       , $nbAlertesInterv);
$smarty->assign("date"                  , $date);
$smarty->assign("listSpec"              , $listSpec);
$smarty->display("vw_edit_planning");
