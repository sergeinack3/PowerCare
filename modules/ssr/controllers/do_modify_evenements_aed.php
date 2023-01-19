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
use Ox\Core\CValue;
use Ox\Mediboard\Ssr\CEvenementSSR;

$event_ids                = CValue::post("event_ids");
$del                      = CValue::post("del", 0);
$_nb_decalage_min_debut   = CValue::post("_nb_decalage_min_debut");
$_nb_decalage_heure_debut = CValue::post("_nb_decalage_heure_debut");
$_nb_decalage_jour_debut  = CValue::post("_nb_decalage_jour_debut");
$_nb_decalage_duree       = CValue::post("_nb_decalage_duree");
$_traitement              = CValue::post("_traitement");
$kine_id                  = CValue::post("kine_id");
$equipement_id            = CValue::post("equipement_id");
$sejour_id                = CValue::post("sejour_id");

$modifiy_evt_everybody = CAppUI::gconf("ssr general modifiy_evt_everybody");
$elts_id               = explode("|", $event_ids);

foreach ($elts_id as $_elt_id) {
  $evenement = new CEvenementSSR();
  $evenement->load($_elt_id);

  if (!$modifiy_evt_everybody) {
    // Autres rééducateurs
    global $can;
    if (!in_array(CAppUI::$instance->user_id, $evenement->getTherapeutes()) && !$can->admin) {
      CAppUI::displayMsg(CAppui::tr("CEvenementSSR-no_modify_evt_other_reeduc"), "CEvenementSSR-msg-modify");
      continue;
    }
  }

  // Suppression des evenements SSR
  if ($del) {
    // Suppression de l'evenement
    $msg = $evenement->delete();
    CAppUI::displayMsg($msg, "CEvenementSSR-msg-delete");
  }
  // Modification des evenements SSR
  else {
    if (
      $_traitement ||
      $_nb_decalage_min_debut ||
      $_nb_decalage_heure_debut ||
      $_nb_decalage_jour_debut ||
      $_nb_decalage_duree ||
      $equipement_id ||
      $kine_id
    ) {
      if ($evenement->_traitement = $_traitement) {
        $evenement->realise = CValue::post("realise");
        $evenement->annule  = CValue::post("annule");
      };

      if ($evenement->seance_collective_id) {
        $evenement = $evenement->loadRefSeanceCollective();
      }

      if ($_nb_decalage_min_debut) {
        $evenement->debut = CMbDT::dateTime("$_nb_decalage_min_debut minutes", $evenement->debut);
      }
      if ($_nb_decalage_heure_debut) {
        $evenement->debut = CMbDT::dateTime("$_nb_decalage_heure_debut hours", $evenement->debut);
      }
      if ($_nb_decalage_jour_debut) {
        $evenement->debut = CMbDT::dateTime("$_nb_decalage_jour_debut days", $evenement->debut);
      }
      if ($_nb_decalage_duree) {
        $evenement->duree = $evenement->duree + $_nb_decalage_duree;
      }
      if ($equipement_id || $equipement_id == 'none') {
        $evenement->equipement_id = ($equipement_id == 'none') ? "" : $equipement_id;
      }
      if ($kine_id) {
        $evenement->therapeute_id = $kine_id;
      }

      //Il faut appliquer les modifications à l'ensemble des événéments enfants de la séance collective
      if (!$evenement->sejour_id) {
        $evenement->loadRefsEvenementsSeance();
        foreach ($evenement->_ref_evenements_seance as $_evt_seance) {
          if ($_evt_seance->_traitement = $_traitement) {
            $_evt_seance->realise = CValue::post("realise");
            $_evt_seance->annule  = CValue::post("annule");
          };
          if ($_evt_seance->realise || $_evt_seance->annule) {
            continue;
          }
          if ($_nb_decalage_min_debut) {
            $_evt_seance->debut = CMbDT::dateTime("$_nb_decalage_min_debut minutes", $_evt_seance->debut);
          }
          if ($_nb_decalage_heure_debut) {
            $_evt_seance->debut = CMbDT::dateTime("$_nb_decalage_heure_debut hours", $_evt_seance->debut);
          }
          if ($_nb_decalage_jour_debut) {
            $_evt_seance->debut = CMbDT::dateTime("$_nb_decalage_jour_debut days", $_evt_seance->debut);
          }
          if ($_nb_decalage_duree) {
            $_evt_seance->duree = $_evt_seance->duree + $_nb_decalage_duree;
          }
          if ($equipement_id || $equipement_id == 'none') {
            $_evt_seance->equipement_id = ($equipement_id == 'none') ? "" : $equipement_id;
          }
          if ($kine_id) {
            $_evt_seance->therapeute_id = $kine_id;
          }
          $msg                              = $_evt_seance->store();
          CAppUI::displayMsg($msg, "CEvenementSSR-msg-modify");
        }
      }

      $msg = $evenement->store();
      CAppUI::displayMsg($msg, "CEvenementSSR-msg-modify");
    }
  }
}

echo CAppUI::getMsg();
CApp::rip();
