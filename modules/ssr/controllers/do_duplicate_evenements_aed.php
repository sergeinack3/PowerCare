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
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\Ssr\CReplacement;

$propagate = CView::post("propagate", "bool");
$_days     = CView::post("_days", "str");
$event_ids = CView::post("event_ids", "str");
$period    = CView::post("period", "str");
$sejour_id = CView::post("sejour_id", "ref class|CSejour");
$date      = CView::post("date", "date default|now", true);
CView::checkin();

$monday = CMbDT::date("last monday", CMbDT::date("+1 day", $date));
$days   = array("");

// Propagation aux autres jours
if ($propagate) {
  $days = array();
  foreach ($_days as $_number) {
    $days[] = CMbDT::date("+$_number DAYS", $monday);
  }
}
elseif ($period == "end_sejour") {
  $days   = array();
  $sejour = new CSejour();
  $sejour->load($sejour_id);
  $sortie_prevue = CMbDT::date($sejour->sortie_prevue);
  $nb_week       = 1;
  for ($i = $monday; $i < $sortie_prevue; $i = CMbDT::date("+1 week", $i)) {
    if ($i < $sortie_prevue) {
      $days[] = $nb_week;
      $nb_week++;
    }
  }
}

$elts_id = explode("|", $event_ids);

//Ajout des séances collectives et des séances filles à la duplication
foreach ($days as $day) {
  foreach ($elts_id as $_elt_id) {
    $evenement = new CEvenementSSR();
    $evenement->load($_elt_id);
    if ($evenement->seance_collective_id) {
      if (!in_array($evenement->seance_collective_id, $elts_id)) {
        array_unshift($elts_id, $evenement->seance_collective_id);
      }
      $seance_collective = $evenement->loadRefSeanceCollective();
      $seances_filles    = $seance_collective->loadRefsEvenementsSeance();
      foreach ($seances_filles as $_seance_fille) {
        if ($_seance_fille->_id != $_elt_id && !in_array($_seance_fille->_id, $elts_id)) {
          $elts_id[] = $_seance_fille->_id;
        }
      }
    }
  }
}

$ids_new_seances_collective = array();
foreach ($days as $key_day => $day) {
  foreach ($elts_id as $_elt_id) {
    $evenement = new CEvenementSSR();
    $evenement->load($_elt_id);
    $evenement->loadRefsActesCdARR();
    $evenement->loadRefsActesCsARR();
    $evenement->loadRefsActesPrestationsSSR();

    // Duplication de l'événement  
    $evenement->_id     = "";
    $evenement->realise = 0;
    $evenement->annule  = 0;
    $period_used        = !$propagate && $period == "end_sejour" ? "+$day week" : $period;
    $evenement->debut   = $propagate ? "$day " . CMbDT::time($evenement->debut) : CMbDT::dateTime($period_used, $evenement->debut);

    if (!$propagate && $period == "end_sejour" && $sejour->sortie_prevue < $evenement->debut) {
      continue;
    }

    // Cas des séances collectives
    if ($evenement->seance_collective_id) {
      $evenement->seance_collective_id = $ids_new_seances_collective[$evenement->seance_collective_id . "-" . $key_day];
    }

    // Autres rééducateurs
    global $can;
    $user       = CAppUI::$user;
    $therapeute = $evenement->loadRefTherapeute();
    if ($evenement->seance_collective_id) {
      $therapeute = $evenement->loadRefSeanceCollective()->loadRefTherapeute();
    }
    if ($therapeute->function_id != $user->function_id && !$can->admin) {
      CAppUI::displayMsg(CAppui::tr("CEvenementSSR-no_duplicate_other_spec"), "CEvenementSSR-msg-create");
      continue;
    }

    if ($evenement->type_seance != "collective") {
      // Chargements préparatoire au transferts automatiques de rééducateurs
      $sejour = new CSejour;
      $sejour->load($evenement->sejour_id);

      $bilan         = $sejour->loadRefBilanSSR();
      $referant      = $bilan->loadRefKineReferent();
      $_day          = CMbDT::date($evenement->debut);
      $therapeute_id = $evenement->therapeute_id;

      // Transfert kiné référent => kiné remplaçant si disponible
      if ($therapeute_id == $referant->_id) {
        $conge = new CPlageConge();
        $conge->loadFor($therapeute_id, $_day);
        // Référent en congés
        if ($conge->_id) {
          $replacement            = new CReplacement();
          $replacement->conge_id  = $conge->_id;
          $replacement->sejour_id = $sejour->_id;
          $replacement->loadMatchingObject();
          if ($replacement->_id) {
            $evenement->therapeute_id = $replacement->replacer_id;
          }
        }
      }

      // Transfert kiné remplacant => kiné référant si présent
      if ($sejour->isReplacer($therapeute_id)) {
        $conge = new CPlageConge();
        $conge->loadFor($referant->_id, $_day);
        // Référent présent
        if (!$conge->_id) {
          $evenement->therapeute_id = $referant->_id;
        }
      }
    }

    $msg = $evenement->store();
    CAppUI::displayMsg($msg, "CEvenementSSR-msg-create");

    // Duplication des codes CdARR
    if ($evenement->_id) {
      //Si c'est la séance collective (support des séances des différents séjours) récupération du nouvel Id
      if ($evenement->type_seance == "collective" && !$evenement->seance_collective_id) {
        $ids_new_seances_collective[$_elt_id . "-" . $key_day] = $evenement->_id;
      }
      else {
        foreach ($evenement->_ref_actes_cdarr as $_acte) {
          $_acte->_id              = "";
          $_acte->evenement_ssr_id = $evenement->_id;
          $msg                     = $_acte->store();
          CAppUI::displayMsg($msg, "CActeCdARR-msg-create");
        }
        foreach ($evenement->_ref_actes_csarr as $_acte) {
          $_acte->_id              = "";
          $_acte->evenement_ssr_id = $evenement->_id;
          $msg                     = $_acte->store();
          CAppUI::displayMsg($msg, "CActeCdARR-msg-create");
        }

        foreach ($evenement->_refs_prestas_ssr as $_presta) {
          $_presta->_id              = "";
          $_presta->evenement_ssr_id = $evenement->_id;
          $msg                       = $_presta->store();
          CAppUI::displayMsg($msg, "CActePrestationSSR-msg-create");
        }
      }
    }
  }
}

//Suppression des séances collectives vides (ex: séance fille non créé car dépassement des dates du séjour)
foreach ($ids_new_seances_collective as $_seance_collective_id) {
  $_seance_collective = new CEvenementSSR();
  $_seance_collective->load($_seance_collective_id);
  $seances = $_seance_collective->loadRefsEvenementsSeance();
  if (!count($seances)) {
    $msg = $_seance_collective->delete();
    CAppUI::displayMsg($msg, "CEvenementSSR-msg-delete");
  }
}


echo CAppUI::getMsg();
CApp::rip();
