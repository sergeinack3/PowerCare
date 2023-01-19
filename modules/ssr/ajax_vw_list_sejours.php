<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Personnel\CPlageConge;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CBilanSSR;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\Ssr\CTechnicien;

global $m;

CCanDo::checkEdit();
$type = CView::get("type", "enum list|kine|reeducateur", true);
$date = CView::get("date", "date default|now", true);
CView::checkin();

// Week dates
$monday = CMbDT::date("last monday", CMbDT::date("+1 DAY", $date));
$sunday = CMbDT::date("next sunday", CMbDT::date("-1 DAY", $date));

// Chargement des conges
$plage_conge         = new CPlageConge();
$where               = array();
$where["date_debut"] = "<= '$sunday'";
$where["date_fin"]   = ">= '$monday'";
$order               = "date_debut DESC, date_fin DESC";

/** @var CPlageConge[] $plages_conge */
$plages_conge = $plage_conge->loadList($where, $order);

// Début et fin d'activite
foreach (CEvenementSSR::getActiveTherapeutes($monday, $sunday) as $_therapeute) {
  // Pseudo plage de début
  if (($deb = $_therapeute->deb_activite) && $deb >= $monday) {
    $plage                     = CPlageConge::makePseudoPlage($_therapeute->_id, "deb", $monday);
    $plages_conge[$plage->_id] = $plage;
  }

  // Pseudo plage de fin
  if (($fin = $_therapeute->fin_activite) && $fin <= $sunday) {
    $plage                     = CPlageConge::makePseudoPlage($_therapeute->_id, "fin", $sunday);
    $plages_conge[$plage->_id] = $plage;
  }
}

/** @var CSejour[] $sejours */
$sejours       = array();
$_sejours      = array();
$count_evts    = array();
$sejours_count = 0;

$group_id = CGroups::loadCurrent()->_id;
// Pour chaque plage de conge, recherche 
foreach ($plages_conge as $_plage_conge) {
  $kine     = $_plage_conge->loadRefUser();
  $_sejours = array();

  $date_min = max($monday, $_plage_conge->date_debut);
  $date_max = CMbDT::date("+1 DAY", min($sunday, $_plage_conge->date_fin));

  // Cas des remplacements kinés
  if ($type == "kine" && !$_plage_conge->_activite) {
    $_sejours = CBilanSSR::loadSejoursSurConges($_plage_conge, $monday, $sunday);
  }

  // Cas des transferts de rééducateurs
  if ($type == "reeducateur") {
    $evenement              = new CEvenementSSR();
    $where                  = array();
    $where["debut"]         = " BETWEEN '$date_min' AND '$date_max'";
    $where["therapeute_id"] = " = '$_plage_conge->user_id'";

    /** @var CEvenementSSR[] $evenements */
    $evenements = $evenement->loadList($where);

    foreach ($evenements as $_evenement) {
      $evts = $_evenement->type_seance == "collective" ? $_evenement->loadRefsEvenementsSeance() : array($_evenement);
      foreach ($evts as $_evt_child) {
        $sejour = $_evt_child->loadRefSejour();
        if ($group_id != $sejour->group_id || $sejour->type != $m) {
          unset($evenements[$_evt_child->_id]);
          continue;
        }
        $bilan = $sejour->loadRefBilanSSR();
        $bilan->loadRefTechnicien();
        $_sejours[$sejour->_id] = $sejour;

        // On compte le nombre d'evenements SSR à transferer
        if (!isset($count_evts["$_plage_conge->_id-$sejour->_id"])) {
          $count_evts["$_plage_conge->_id-$sejour->_id"] = 0;
        }
        $count_evts["$_plage_conge->_id-$sejour->_id"]++;
      }
    }
  }

  foreach ($_sejours as $_sejour) {
    $_sejour->checkDaysRelative($date);
    $replacement = $_sejour->loadRefReplacement($_plage_conge->_id);
    if (!$replacement->_id || $type == "reeducateur") {
      $sejours_count++;
    }

    if ($replacement->_id || $type == "kine") {
      $replacement->loadRefReplacer()->loadRefFunction();
    }

    if (!$replacement->_id && $type == "kine") {
      $replacement->_ref_guessed_replacers = CEvenementSSR::getAllTherapeutes($_sejour->patient_id, $kine->function_id);
      unset($replacement->_ref_guessed_replacers[$kine->_id]);
    }

    // Bilan SSR
    $bilan = $_sejour->loadRefBilanSSR();

    // Kine principal
    /** @var CTechnicien $technicien */
    $technicien = $bilan->loadFwdRef("technicien_id");
    $technicien->loadRefKine()->loadRefFunction();

    // Patient
    $patient = $_sejour->loadRefPatient();
    $patient->loadIPP();
  }

  if (count($_sejours)) {
    $sejours[$_plage_conge->_id] = $_sejours;
  }
}

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("sejours", $sejours);
$smarty->assign("sejours_count", $sejours_count);
$smarty->assign("plages_conge", $plages_conge);
$smarty->assign("type", $type);
$smarty->assign("count_evts", $count_evts);
$smarty->display("inc_vw_list_sejours");
