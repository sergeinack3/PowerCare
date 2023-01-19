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
use Ox\Mediboard\Ssr\CActeCsARR;
use Ox\Mediboard\Ssr\CEvenementSSR;

$modulateurs       = CView::post("modulateurs", "str");
$phases            = CView::post("phases", "str");
$extensions        = CView::post("extensions_doc", "str");
$commentaires      = CView::post("commentaires", "str");
$nb_intervs        = CView::post("nb_interv", "str");
$nb_patients       = CView::post("nb_patient", "str");
$transmissions     = CView::post("transmissions", "str");
$event_ids_realise = CView::post("realise_ids", "str");
$event_ids_annule  = CView::post("annule_ids", "str");
$heures            = CView::post("heures", "str");
$durees            = CView::post("durees", "str");
CView::checkin();
$modifiy_evt_everybody = CAppUI::gconf("ssr general modifiy_evt_everybody");
// Ajustement des actes CsARR
/** @var CActeCsARR[] $actes */
$actes = array();

// Récupération des modulateurs
$modulateurs = $modulateurs ? explode("|", $modulateurs) : array();
foreach ($modulateurs as $_modulateur) {
  list($acte_id, $modulateur) = explode("-", $_modulateur);

  if (!isset($actes[$acte_id])) {
    $acte = new CActeCsARR();
    $acte->load($acte_id);
    $acte->_modulateurs = array();
    $acte->_phases      = array();
    $actes[$acte_id]    = $acte;
  }

  $acte                 = $actes[$acte_id];
  $acte->_modulateurs[] = $modulateur;
}

// Récupération des phases
$phases = $phases ? explode("|", $phases) : array();
foreach ($phases as $_phase) {
  list($acte_id, $phase) = explode("-", $_phase);
  if (!isset($actes[$acte_id])) {
    $acte = new CActeCsARR();
    $acte->load($acte_id);
    $acte->_modulateurs = array();
    $acte->_phases      = array();
    $actes[$acte_id]    = $acte;
  }

  $acte            = $actes[$acte_id];
  $acte->_phases[] = $phase;
}
// Récupération des extensions documentaires
$extensions = $extensions ? explode("|", $extensions) : array();
foreach ($extensions as $_extension) {
  list($acte_id, $extension) = explode("-", $_extension);
  if (!isset($actes[$acte_id])) {
    $acte = new CActeCsARR();
    $acte->load($acte_id);
    $acte->_modulateurs = array();
    $acte->_phases      = array();
    $actes[$acte_id]    = $acte;
  }
  $acte            = $actes[$acte_id];
  $acte->extension = $extension;
}

// Récupération des commentaires
$commentaires = $commentaires ? explode("|", $commentaires) : array();
foreach ($commentaires as $key => $_commentaire) {
  list($commentaire_id) = explode("-", $_commentaire);
  $explode     = explode("_", $commentaire_id);
  $acte_id     = $explode[1];
  $commentaire = str_replace($commentaire_id . "-", '', $_commentaire);
  if (!isset($actes[$acte_id])) {
    $acte = new CActeCsARR();
    $acte->load($acte_id);
    $acte->_modulateurs = array();
    $acte->_phases      = array();
    $actes[$acte_id]    = $acte;
  }
  $acte              = $actes[$acte_id];
  $acte->commentaire = $commentaire;
}

// Enregistrements des actes ajustés
foreach ($actes as $_acte) {
  $msg = $_acte->store();
  CAppUI::displayMsg($msg, "CActeCsARR-msg-modify");
}

// Nombre d'intervenant pour les séances collectives
$nb_inter_by_evnt = array();
if ($nb_intervs) {
  foreach (explode("|", $nb_intervs) as $_nb_interv) {
    list($evenement_id, $nb_interv) = explode("-", $_nb_interv);
    $nb_inter_by_evnt[$evenement_id] = $nb_interv;
  }
}

// Nombre de patient pour les séances collectives
$nb_patient_by_evnt = array();
if ($nb_patients) {
  foreach (explode("|", $nb_patients) as $_nb_patient) {
    list($evenement_id, $nb_patient) = explode("-", $_nb_patient);
    $nb_patient_by_evnt[$evenement_id] = $nb_patient;
  }
}
//Récupération des modifications de l'heure de début de l'événement
$heures_evnt = array();
if ($heures) {
  foreach (explode("|", $heures) as $_heures) {
    list($evenement_id, $heure) = explode("-", $_heures);
    $heures_evnt[$evenement_id] = $heure;
  }
}
//Récupération des modifications de la durée de l'événement
$durees_evnt = array();
if ($durees) {
  foreach (explode("|", $durees) as $_duree) {
    list($evenement_id, $duree) = explode("-", $_duree);
    $durees_evnt[$evenement_id] = $duree;
  }
}

//Transmissions depuis la validation des actes
$transmission_by_evnt = array();
if ($transmissions) {
  foreach (explode("|", $transmissions) as $_transmission) {
    list($id_transmission) = explode("-", $_transmission);
    $explode_id                          = explode("_", $id_transmission);
    $evenement_id                        = $explode_id[1];
    $transmission                        = str_replace($id_transmission . "-", '', $_transmission);
    $transmission_by_evnt[$evenement_id] = $transmission;
  }
}

$seances_collective = array();
// Réalisation des événements
$event_ids = $event_ids_realise ? explode("|", $event_ids_realise) : array();
foreach ($event_ids as $_event_id) {
  $evenement = new CEvenementSSR();
  $evenement->load($_event_id);

  // Autres rééducateurs
  global $can;
  if ($evenement->seance_collective_id) {
    $seances_collective[$evenement->seance_collective_id] = $evenement;
  }
  if (!$modifiy_evt_everybody && !in_array(CAppUI::$instance->user_id, $evenement->getTherapeutes()) && !$can->admin) {
    CAppUI::displayMsg(CAppui::tr("CEvenementSSR-no_modify_evt_other_reeduc"), "CEvenementSSR-msg-modify");
    continue;
  }

  // Ajout du nombre de patient présent aux evenements SSR
  if (isset($nb_patient_by_evnt[$_event_id])) {
    $evenement->nb_patient_seance = $nb_patient_by_evnt[$_event_id];
  }
  if (isset($nb_inter_by_evnt[$_event_id])) {
    $evenement->nb_intervenant_seance = $nb_inter_by_evnt[$_event_id];
  }
  if (isset($transmission_by_evnt[$_event_id])) {
    $evenement->_transmission = $transmission_by_evnt[$_event_id];
  }
  // Enregistrement des evenements SSR
  $evenement->realise     = "1";
  $evenement->annule      = "0";
  $evenement->_traitement = "1";
  if (isset($heures_evnt[$_event_id])) {
    $evenement->debut = CMbDT::date($evenement->debut)." ".$heures_evnt[$_event_id];
  }
  if (isset($durees_evnt[$_event_id])) {
    $evenement->duree = $durees_evnt[$_event_id];
  }
  $msg                    = $evenement->store();
  CAppUI::displayMsg($msg, "CEvenementSSR-msg-modify");
}

// Annulation
$event_ids = $event_ids_annule ? explode("|", $event_ids_annule) : array();
foreach ($event_ids as $_event_id) {
  $evenement = new CEvenementSSR();
  $evenement->load($_event_id);

  // Autres rééducateurs
  global $can;
  if ($evenement->seance_collective_id) {
    $seances_collective[$evenement->seance_collective_id] = $evenement;
  }
  if (!$modifiy_evt_everybody && !in_array(CAppUI::$instance->user_id, $evenement->getTherapeutes()) && !$can->admin) {
    CAppUI::displayMsg(CAppui::tr("CEvenementSSR-no_modify_evt_other_reeduc"), "CEvenementSSR-msg-modify");
    continue;
  }

  if (isset($transmission_by_evnt[$_event_id])) {
    $evenement->_transmission = $transmission_by_evnt[$_event_id];
  }
  // Suppression des evenements SSR
  $evenement->realise     = "0";
  $evenement->annule      = "1";
  $evenement->_traitement = "1";
  if (isset($durees_evnt[$_event_id])) {
    $evenement->duree = $durees_evnt[$_event_id];
  }
  $msg                    = $evenement->store();
  CAppUI::displayMsg($msg, "CEvenementSSR-msg-modify");
}

foreach ($seances_collective as $key => $event) {
  $collectif = new CEvenementSSR();
  $collectif->load($key);
  $old_realise = $collectif->realise;
  $old_annule  = $collectif->annule;
  $realise     = 1;
  $annule      = 0;
  foreach ($collectif->loadRefsEvenementsSeance() as $_event_seance) {
    if (!$_event_seance->realise && !$_event_seance->annule) {
      $realise = 0;
    }
    if ($_event_seance->annule) {
      $annule++;
    }
  }
  $collectif->_traitement = 1;
  $collectif->annule      = 0;
  if ($annule == count($collectif->_ref_evenements_seance)) {
    $collectif->annule = 1;
    $realise           = 0;
  }
  $collectif->realise = $realise;

  if ($old_realise != $collectif->realise || $old_annule != $collectif->annule) {
    $msg = $collectif->store();
    CAppUI::displayMsg($msg, "CEvenementSSR-msg-modify");
  }
}

echo CAppUI::getMsg();
CApp::rip();
