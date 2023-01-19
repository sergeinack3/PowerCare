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
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CPlageOp;

CCanDo::checkRead();

$date_min = CValue::get("_datetime_min", CMbDT::date());
$date_max = CValue::get("_datetime_max");

$bloc_id         = CValue::get("_bloc_id", null);
$_print_annulees = CValue::get("_print_annulees");

if (is_array($bloc_id)) {
  CMbArray::removeValue("0", $bloc_id);
}

$bloc  = new CBlocOperatoire();
$blocs = $bloc->loadGroupList();

if ($bloc_id) {
  $blocs = array_intersect_key($blocs, array_flip($bloc_id));
}

$date_min = CMbDT::date("LAST SUNDAY +1 DAY", $date_min);

if (!$date_max) {
  $date_max = CMbDT::date("+1 WEEK -1 DAY", $date_min);
}
else {
  $date_max = CMbDT::date("NEXT MONDAY -1 DAY", $date_max);
}


// Initialisation du tableau
$results = array();
$dates = array();
$dates_planning = array();

$plage = new CPlageOp();

$hour_midi = CAppUI::gconf("dPbloc printing hour_midi_fullprint");

for ($date_temp = $date_min ; $date_temp <= $date_max ; $date_temp = CMbDT::date("+1 WEEK", $date_temp)) {
  $ljoin = array();
  $where = array();
  
  $_date_min = $date_temp;
  $_date_max = CMbDT::date("+1 WEEK -1 DAY", $date_temp);
  
  $dates_planning[$date_temp] = $date_temp;
  
  $results[$date_temp] = array(
    "date_min" => $_date_min,
    "date_max" => $_date_max,
  );
  $dates[$date_temp] = array();
  
  for ($date = $_date_min; $date <= $_date_max; $date = CMbDT::date("+1 DAY", $date)) {
    $dates[$date_temp][$date] = $date;
  }
  
  // On teste si l'on peut retirer le dimanche
  $where["date"] = "= '$date_max'";
  
  if ($bloc_id) {
    $ljoin["sallesbloc"] = "sallesbloc.salle_id = plagesop.salle_id";
    $where["sallesbloc.bloc_id"] = CSQLDataSource::prepareIn(array_keys($blocs));
  }
  
  if ($plage->countList($where, null, $ljoin) == 0) {
    array_pop($dates[$date_temp]);
  }
  
  // Puis le samedi
  $where["date"] = "= '".CMbDT::date("-1 day", $date_max)."'";
  if ($plage->countList($where, null, $ljoin) == 0) {
    array_pop($dates[$date_temp]);
  }
  
  unset($where["sallesbloc.bloc_id"]);
  
  foreach ($blocs as $_bloc) {
    foreach ($_bloc->_ref_salles as $_salle) {
      foreach ($dates[$date_temp] as $date) {
        $where["salle_id"] = "= '$_salle->_id'";
        $results[$date_temp][$_salle->_id][$date] = array("am" => "", "pm" => "");
        
        foreach ($results[$date_temp][$_salle->_id][$date] as $key => &$_result_by_creneau) {
          $where["date"] = " = '$date'";
          if ($key == "am") {
            $where["debut"] = "<= '$hour_midi:00:00'";
          }
          else {
            $where["debut"] = "> '$hour_midi:00:00'";
          }
          /** @var CPlageOp[] $plages */
          $plages = $plage->loadList($where);
          
          foreach ($plages as $_plage) {
            $chir = $_plage->loadRefChir();
            $_result_by_creneau = $chir->_user_last_name;
            
            $whereOp = array();
            $ljoin = array();

            if (!$_print_annulees) {
              $whereOp["annulee"] = "= '0'";
            }

            $ljoin["sejour"] = "sejour.sejour_id = operations.sejour_id";
            
            $whereOp["type"] = "= 'comp'";
            $comp = $_plage->countBackRefs("operations", $whereOp, $ljoin);
            
            $whereOp["type"] = "= 'ambu'";
            $ambu = $_plage->countBackRefs("operations", $whereOp, $ljoin);
            
            $whereOp["type"] = "= 'exte'";
            $exte = $_plage->countBackRefs("operations", $whereOp, $ljoin);
            
            // Dans chaque case, format (HX/AX/EX)
            // H => hospi complète
            // A => ambulatoire
            // E => externe
            
            if ($comp || $ambu || $exte) {
              $_result_by_creneau .= " (";
              if ($comp) {
                $_result_by_creneau .= "H$comp";
              }
              if ($ambu) {
                if ($comp) {
                  $_result_by_creneau .= "/";
                }
                $_result_by_creneau .= "A$ambu";
              }
              if ($exte) {
                if ($comp || $ambu) {
                  $_result_by_creneau .= "/";
                }
                $_result_by_creneau .= "E$exte";
              }
              $_result_by_creneau .= ")";
            }
            $_result_by_creneau .= "\n";
          }
        }
      }
    } 
  }
}
// Création du template
$smarty = new CSmartyDP();

$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("results", $results);
$smarty->assign("blocs" , $blocs);
$smarty->assign("dates" , $dates);
$smarty->assign("dates_planning" , $dates_planning);

$smarty->display("print_full_planning.tpl");
