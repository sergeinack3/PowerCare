<?php
/**
 * @package Mediboard\Cabinet
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
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$ds = CSQLDataSource::get("std");

// Initialisation des variables
$plageconsult_id    = CValue::get("plageconsult_id");
$consult_id         = CValue::get("consult_id");
$slot_id            = CValue::get("slot_id");
$heure              = CValue::get("heure");
$multiple           = (bool)CValue::get("multiple", false);
$display_nb_consult = CAppUI::gconf("dPcabinet PriseRDV display_nb_consult");
$quotas             = null;

// Récupération des consultations de la plage séléctionnée
$plage = new CPlageconsult;
if ($plageconsult_id) {
  $plage->load($plageconsult_id);
  $plage->loadRefsNotes();

  //Chargement du lieu associé à la plage
  $plage->loadRefAgendaPraticien();

  $date  = $plage->date;
}
else {
  $date  = CValue::get("date", CMbDT::date());
}


// consultation précise
$consultation_target = new CConsultation();
if ($consult_id) {
  $consultation_target->load($consult_id);
}
elseif ($heure) {
  $consultation_target->heure = $heure;
}
$consultation_target->loadRefElementPrescription();


// Chargement des places disponibles
$listPlace   = array();
$listBefore  = array();
$listAfter   = array();
$next_plage = $previous_plage = new CPlageconsult();
$function_id = null;

if ($plageconsult_id) {
  if (!$plage->plageconsult_id) {
    $plage->load($plageconsult_id);
  }
  $plage->loadRefsFwd(true);
  $function_id = $plage->_ref_chir->function_id;
  $plage->loadRefsConsultations(false, true, true);
  $plage->loadFillRate();
  $plage->_ref_chir->loadRefFunction();

  //Chargement du lieu associé à la plage
  $plage->loadRefAgendaPraticien();

  for ($i = 0; $i < $plage->_total; $i++) {
    $minutes = $plage->_freq * $i;
    $listPlace[$i]["time"]          = CMbDT::time("+ $minutes minutes", $plage->debut);
    $listPlace[$i]["consultations"] = array();
  }
  
  foreach ($plage->_ref_consultations as $keyConsult => $valConsult) {
    $consultation =& $plage->_ref_consultations[$keyConsult];
    $consultation->loadRefPatient();
    // Chargement de la categorie
    $consultation->loadRefCategorie();
    
    $keyPlace = CMbDT::timeCountIntervals($plage->debut, $consultation->heure, $plage->freq);
  
    if ($keyPlace < 0) {
      $listBefore[$keyPlace] =& $consultation;
    }
    
    if ($consultation->heure >= $plage->fin) {
      $listAfter[$keyPlace] =& $consultation;
    }
    
    for ($i = 0;  $i < $consultation->duree; $i++) {
      if (isset($listPlace[($keyPlace + $i)])) {
        $listPlace[($keyPlace + $i)]["consultations"][] =& $consultation;
      }
    }
  }
  
  // Utilisation des prises de rdv
  // Pour ceux de la même fonction
  $user = new CMediusers;
  $user->load($plage->chir_id);
  $function = $user->loadRefFunction();
  if ($function->quotas) {
    $quotas = $function->quotas;
  }

  $listPrat    = CConsultation::loadPraticiens(PERM_EDIT, $user->function_id, null, true);
  $listAllPrat = CConsultation::loadPraticiens(null, null, null, true);

  $where = array();
  $where["date"]    = $ds->prepare("BETWEEN %1 AND %2", "$plage->date", "$plage->date");
  $where[]          = "libelle != 'automatique' OR libelle IS NULL";
  $where["chir_id"] = " = '$user->_id'";

  if ($display_nb_consult == "cab" || $display_nb_consult == "etab") {
    $where["chir_id"] = CSQLDataSource::prepareIn(array_keys($listPrat));
    /** @var CPlageconsult[] $plages_func */
    $plages_func      = $plage->loadList($where);
    $utilisation_func = utilisation_rdv($plages_func, $listPlace, $plage);
  }
  if ($display_nb_consult == "etab") {
    $where["chir_id"] = CSQLDataSource::prepareIn(array_keys($listAllPrat));
    /** @var CPlageconsult[] $plages_etab */
    $plages_etab      = $plage->loadList($where);
    $utilisation_etab = utilisation_rdv($plages_etab, $listPlace, $plage);
  }

  // next consult
  $next_plage = $plage->getNextPlage();

  // previous consult
  $previous_plage = $plage->getPreviousPlage();
}

// user's function available
$mediuser = new CMediusers();
$mediusers = $mediuser->loadProfessionnelDeSanteByPref(PERM_READ, $function_id);

// Vérifier le droit d'écriture sur la plage sélectionnée
$plage->canDo();
$plage->checkLimitHours();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("plageconsult_id", $plageconsult_id);
$smarty->assign("plage"          , $plage);
$smarty->assign("listPlace"      , $listPlace);
$smarty->assign("next_plage"     , $next_plage);
$smarty->assign("list_users"     , $mediusers);
$smarty->assign("previous_plage" , $previous_plage);
$smarty->assign("listBefore"     , $listBefore);
$smarty->assign("listAfter"      , $listAfter);
$smarty->assign("quotas"         , $quotas);
$smarty->assign("multipleMode"   , (int)$multiple);
$smarty->assign("consultation"   , $consultation_target);
$smarty->assign("date"           , $date);
$smarty->assign('slot_id'        , $slot_id);
$smarty->assign('heure'          , $heure);

if ($display_nb_consult == "cab" || $display_nb_consult == "etab") {
  $smarty->assign("utilisation_func", $utilisation_func);
}
if ($display_nb_consult == "etab") {
  $smarty->assign("utilisation_etab", $utilisation_etab);
}

$smarty->assign("online"         , true);

$smarty->display("inc_list_places.tpl");


/**
 * Calcul le taux d'utilisation de prise de rendez-vous par créneaux de 5 minutes
 *
 * @param CPlageconsult[] $plages Plages
 * @param array           $list   Liste
 * @param CPlageconsult   $plage  Plage
 *
 * @return array
 */
function utilisation_rdv($plages, $list, $plage) {
  $utilisation = array();

  // Granularité de 5 minutes.
  // 288 créneaux de 5 minutes dans 24 heures
  for ($i=0 ; $i < 288 ; $i++) {
    $time = CMbDT::time(($i*5)." minutes", $plage->debut);
    $utilisation[$time] = 0;
    if ($time == $plage->fin) {
      break;
    }
  }

  foreach ($plages as $_plage) {
    $rdvs = $_plage->loadRefsConsultations(false);
    $freq = CMbDT::transform($_plage->freq, null, "%M");

    foreach ($rdvs as $_rdv) {
      $nb_cases = ($_rdv->duree * $freq) / 5 ;
      for ($i=0 ; $i < $nb_cases ; $i++) {

        $time = CMbDT::time(($i*5)." minutes", $_rdv->heure);
        if (!isset($utilisation[$time])) {
          continue;
        }
        $utilisation[$time] ++;
      }
    }
  }

  ksort($utilisation);

  // Granularité à la fréquence des consultations de la plage
  $creneaux = array_flip(CMbArray::pluck($list, "time"));
  $save_key = 0;

  foreach ($utilisation as $key => $_util) {
    if (!isset($creneaux[$key]) && isset($utilisation[$save_key])) {
      $utilisation[$save_key] = max($_util, $utilisation[$save_key]);
      unset($utilisation[$key]);
    }
    else {
      $utilisation[$key] = $_util;
      $save_key = $key;
    }
  }

  return $utilisation;
}