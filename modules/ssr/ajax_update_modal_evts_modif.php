<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CEvenementSSR;

CCanDo::checkRead();
$token_evts         = CView::get("token_evts", "str", true);
$refresh_validation = CView::get("refresh_validation", "bool default|0");
CView::checkin();

$_evenements = array();
if ($token_evts) {
  $_evenements = explode("|", $token_evts);
}

$count_actes = $actes = array(
  "cdarr"   => array(), "csarr"   => array(),
  "prestas" => array(),
);

$evenements = array();
foreach ($_evenements as $_evenement_id) {
  $evenement = new CEvenementSSR();
  $evenement->load($_evenement_id);

  if ($evenement->seance_collective_id) {
    // Recuperation des informations de la seance collective
    $evenement->loadRefSeanceCollective();
    $evenement->debut = $evenement->_ref_seance_collective->debut;
    $evenement->duree = $evenement->_ref_seance_collective->duree;
  }

  $evenement->loadRefSejour()->loadRefPatient();

  // Chargement et comptage des codes de tous les actes
  foreach ($evenement->loadRefsActes() as $_type => $_actes) {
    foreach ($_actes as $_acte) {
      $actes[$_type][$_acte->code] = $_acte->code;
      if (!isset($count_actes[$_type][$_acte->code])) {
        $count_actes[$_type][$_acte->code] = 0;
      }
      if ($_acte->quantite > $count_actes[$_type][$_acte->code]) {
        $count_actes[$_type][$_acte->code] = $_acte->quantite;
      }
    }
  }

  // Chargement des codes possibles pour l'evenement
  $line    = $evenement->loadRefPrescriptionLineElement();
  $element = $line->_ref_element_prescription;
  foreach ($element->loadRefsCodesSSR() as $_type => $_links) {
    foreach ($_links as $_link_acte) {
      $actes[$_type][$_link_acte->code] = $_link_acte->code;
    }
  }

  $evenements[$evenement->_id] = $evenement;
}

if (CAppUI::gconf("ssr general use_acte_presta") == 'csarr') {
  unset($actes['prestas']);
}
elseif (CAppUI::gconf("ssr general use_acte_presta") == 'presta') {
  unset($actes['cdarr'], $actes['csarr']);
}
$types = array_keys($actes);

// Sorting
ksort($actes);
foreach ($actes as $_type => &$_actes) {
  ksort($_actes);
}

if (!count($count_actes["cdarr"])) {
  unset($actes["cdarr"], $count_actes["cdarr"]);
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("token_evts"  , $token_evts);
$smarty->assign("evenements"  , $evenements);
$smarty->assign("actes"       , $actes);
$smarty->assign("count_actes" , $count_actes);
$smarty->assign("refresh_validation", $refresh_validation);
$smarty->assign("types"       , $types);
$smarty->display("inc_vw_modal_evts_modif");
