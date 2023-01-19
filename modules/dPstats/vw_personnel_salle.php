<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$deb_personnel  = CValue::getOrSession("deb_personnel", CMbDT::date("-1 WEEK"));
$fin_personnel  = CValue::getOrSession("fin_personnel", CMbDT::date(""));
$prat_personnel = CValue::getOrSession("prat_personnel", null);

CView::enforceSlave();

$user      = CMediusers::get();
$listPrats = $user->loadPraticiens(PERM_READ);

$total["duree_prevue"]             = "00:00:00";
$total["days_duree_prevue"]        = 0;
$total["duree_first_to_last"]      = "00:00:00";
$total["days_duree_first_to_last"] = 0;
$total["duree_reelle"]             = "00:00:00";
$total["days_duree_reelle"]        = 0;
$total["personnel"]                = array(
  "iade"        => array("days_duree" => 0, "duree" => "00:00:00"),
  "op"          => array("days_duree" => 0, "duree" => "00:00:00"),
  "op_panseuse" => array("days_duree" => 0, "duree" => "00:00:00"));

// Récupération des plages
$plage      = new CPlageOp;
$listPlages = array();
if ($prat_personnel) {
  $where            = array();
  $where["date"]    = "BETWEEN '$deb_personnel' AND '$fin_personnel'";
  $where["chir_id"] = "= '$prat_personnel'";
  $order            = "date, salle_id, debut";
  /** @var CPlageOp[] $listPlages */
  $listPlages = $plage->loadList($where, $order);

  // Récupération des interventions
  foreach ($listPlages as &$curr_plage) {
    /*
     * Chargement des interventions et des éléments suivants :
     * - durée prévue
     * - nombre d'interventions
     * - nombre d'interventions valides
     * - temps des interventions
     * - nombre de panseuses
     * - nombre d'aides op
     */
    $curr_plage->loadRefChir();
    $curr_plage->loadRefAnesth();
    $curr_plage->loadRefSpec();
    $curr_plage->loadRefSalle();
    $curr_plage->loadRefsOperations(false);

    $curr_plage->_first_op              = "23:59:59";
    $curr_plage->_last_op               = "00:00:00";
    $curr_plage->_duree_total_op        = "00:00:00";
    $curr_plage->_duree_first_to_last   = "00:00:00";
    $curr_plage->_op_for_duree_totale   = 0;
    $curr_plage->_duree_total_personnel = array();

    // Personnel de la plage
    $curr_plage->loadAffectationsPersonnel();
    foreach ($curr_plage->_ref_operations as $curr_op) {
      // Durées
      if ($curr_op->debut_op && $curr_op->fin_op && ($curr_op->debut_op < $curr_op->fin_op)) {
        $curr_plage->_first_op       = min($curr_plage->_first_op, $curr_op->debut_op);
        $curr_plage->_last_op        = max($curr_plage->_last_op, $curr_op->fin_op);
        $duree_op                    = CMbDT::timeRelative($curr_op->debut_op, $curr_op->fin_op);
        $curr_plage->_duree_total_op = CMbDT::addTime($duree_op, $curr_plage->_duree_total_op);
        $curr_plage->_op_for_duree_totale++;
      }
      // Personnel réel
      $curr_op->loadAffectationsPersonnel();

      foreach ($curr_op->_ref_affectations_personnel as $_key_cat => $_curr_cat) {

        if (!isset($curr_plage->_duree_total_personnel[$_key_cat])) {
          $curr_plage->_duree_total_personnel[$_key_cat]["duree"]      = "00:00:00";
          $curr_plage->_duree_total_personnel[$_key_cat]["days_duree"] = "0";
        }

        foreach ($_curr_cat as $_curr_aff) {
          if ($_curr_aff->debut && $_curr_aff->fin) {
            $duree     = CMbDT::timeRelative($_curr_aff->debut, $_curr_aff->fin);
            $new_total = CMbDT::addTime($duree, $curr_plage->_duree_total_personnel[$_key_cat]["duree"]);
            if ($new_total < $curr_plage->_duree_total_personnel[$_key_cat]["duree"]) {
              $curr_plage->_duree_total_personnel[$_key_cat]["days_duree"]++;
            }
            $curr_plage->_duree_total_personnel[$_key_cat]["duree"] = $new_total;
          }
        }
      }
    }
    // Totaux
    // Durée prévue
    $newTotalPrevu = CMbDT::addTime($curr_plage->_duree_prevue, $total["duree_prevue"]);
    if ($newTotalPrevu < $total["duree_prevue"]) {
      $total["days_duree_prevue"]++;
    }
    $total["duree_prevue"] = $newTotalPrevu;
    // Durée première à la dernière
    if ($curr_plage->_first_op && $curr_plage->_last_op && ($curr_plage->_first_op < $curr_plage->_last_op)) {
      $curr_plage->_duree_first_to_last = CMbDT::timeRelative($curr_plage->_first_op, $curr_plage->_last_op);

      $newTotalFirstToLast = CMbDT::addTime($curr_plage->_duree_first_to_last, $total["duree_first_to_last"]);
      if ($newTotalFirstToLast < $total["duree_first_to_last"]) {
        $total["days_duree_first_to_last"]++;
      }
      $total["duree_first_to_last"] = $newTotalFirstToLast;
    }
    // Durée réèlle
    $newTotalReel = CMbDT::addTime($curr_plage->_duree_total_op, $total["duree_reelle"]);
    if ($newTotalReel < $total["duree_reelle"]) {
      $total["days_duree_reelle"]++;
    }
    $total["duree_reelle"] = $newTotalReel;
    // Durée du personnel

    foreach ($curr_plage->_duree_total_personnel as $_key_cat => $_curr_cat) {
      if (!isset($total["personnel"][$_key_cat])) {
        $total["personnel"][$_key_cat]["duree"]      = "00:00:00";
        $total["personnel"][$_key_cat]["days_duree"] = 0;
      }
      $newTotalPersonnel                           = CMbDT::addTime(
        $curr_plage->_duree_total_personnel[$_key_cat]["duree"], $total["personnel"][$_key_cat]["duree"]
      );
      $total["personnel"][$_key_cat]["days_duree"] += $curr_plage->_duree_total_personnel[$_key_cat]["days_duree"];
      if ($newTotalPersonnel < $total["personnel"][$_key_cat]["duree"]) {
        $total["personnel"][$_key_cat]["days_duree"]++;
      }
      $total["personnel"][$_key_cat]["duree"] = $newTotalPersonnel;
    }
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("listPrats", $listPrats);
$smarty->assign("deb_personnel", $deb_personnel);
$smarty->assign("fin_personnel", $fin_personnel);
$smarty->assign("prat_personnel", $prat_personnel);
$smarty->assign("listPlages", $listPlages);
$smarty->assign("total", $total);

$smarty->display("vw_personnel_salle.tpl");
