<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\Ssr\CExtensionDocumentaireCsARR;
use Ox\Mediboard\Ssr\CPlageGroupePatient;

CCanDo::checkRead();
$token_field_evts          = CView::get("token_field_evts", "str", true);
$token_field_plages_groupe = CView::get("token_field_plages_groupe", "str", true);
$kine_id                   = CView::get("kine_id", "ref class|CMediusers", true);
$date                      = CView::get("date", "date");
CView::checkin();

/** @var CSejour[] $sejours */
$sejours = array();
/** @var CEvenementSSR[] $events */
$events = array();

$_evenements = $token_field_evts ? explode("|", $token_field_evts) : array();
foreach ($_evenements as $_evenement_id) {
  $evenement = new CEvenementSSR();
  $evenement->load($_evenement_id);

  if ($evenement->sejour_id) {
    $events[$evenement->_id] = $evenement;
  }
  else {
    $evenement->loadRefsEvenementsSeance();
    foreach ($evenement->_ref_evenements_seance as $_evt_seance) {
      $_evt_seance->debut = $evenement->debut;
      $_evt_seance->duree = $evenement->duree;

      $events[$_evt_seance->_id] = $_evt_seance;
    }
  }
}

$plage_groupe_ids = $token_field_plages_groupe ? explode("|", $token_field_plages_groupe) : array();
$first_day_week = CMbDT::date("this week monday", $date);
$last_day_week  = CMbDT::date("this week sunday", $date);

foreach ($plage_groupe_ids as $_plage_groupe_id) {
  $plage_groupe = CPlageGroupePatient::find($_plage_groupe_id);
  $where = array("therapeute_id" => " = '$kine_id'",
                 "debut" => "BETWEEN '$first_day_week 00:00:00' AND '$last_day_week 23:59:59'");
  $events_ssr = $plage_groupe->loadRefEvenementsSSR($where);

  foreach ($events_ssr as $_event_ssr) {
    $events[$_event_ssr->_id] = $_event_ssr;
  }
}

$count_zero_actes       = 0;
$evenements             = array();
$validation_actes_futur = CAppUI::gconf("ssr validation validation_actes_futur");
$date_now               = CMbDT::date();
$csarr_activites        = array();

foreach ($events as $_event) {
  $_event->loadRefEquipement();

  $actes_cdarr = $_event->loadRefsActesCdarr();
  $actes_csarr = $_event->loadRefsActesCsarr();
  $_event->loadRefsActesPrestationsSSR();
  foreach ($actes_csarr as $_acte_csarr) {
    $_acte_csarr->loadRefActiviteCsARR();
  }

  $_event->_count_actes = count($actes_cdarr) + count($actes_csarr) + count($_event->_refs_prestas_ssr);
  if (!$_event->_count_actes) {
    $count_zero_actes++;
  }

  //Si la configuration n'autorise pas la validation des actes dans le futur, il ne doit pas être sélectionnable
  if (!$validation_actes_futur && CMbDT::date($_event->debut) > $date_now) {
    $_event->_no_validation = true;
  }

  if ($_event->seance_collective_id) {
    $_event->loadRefSeanceCollective()->loadRefsEvenementsSeance();
  }

  $sejour = $_event->loadRefSejour();
  $sejour->loadRefPatient();
  $sejours[$sejour->_id]                                                   = $sejour;
  $line                                                                    = $_event->loadRefPrescriptionLineElement();
  $element_id                                                              = $line ? $line->element_prescription_id : null;
  $date_debut                                                              = CMbDT::date($_event->debut);
  $evenements[$_event->sejour_id][$element_id . $date_debut][$_event->_id] = $_event;
}

/** @var array $_evenements_by_sejour */
foreach ($evenements as &$_evenements_by_sejour) {
  ksort($_evenements_by_sejour);
}

$extensions_doc = CExtensionDocumentaireCsARR::getList();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("evenements"      , $evenements);
$smarty->assign("sejours"         , $sejours);
$smarty->assign("count_zero_actes", $count_zero_actes);
$smarty->assign("extensions_doc"  , $extensions_doc);
$smarty->display("inc_vw_modal_evenements");
