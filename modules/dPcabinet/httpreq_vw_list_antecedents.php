<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::check();
$patient_id        = CView::get("patient_id", "ref class|CPatient default|0", true);
$_is_anesth        = CView::get("_is_anesth", "bool default|0", true);
$sejour_id         = CView::get("sejour_id", "ref class|CSejour", true);
$sort_by_date      = CView::get("sort_by_date", "bool default|0", true);
$dossier_anesth_id = CView::get("dossier_anesth_id", "num");
$type_see          = CView::get("type_see", "str", true);
$show_gestion_tp   = CView::get("show_gestion_tp", "bool default|1");
$context_date_min  = CView::get('context_date_min', 'date');
$context_date_max  = CView::get('context_date_max', 'date');
CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

CAccessMedicalData::logAccess($patient);

// Chargement du dossier medical du patient
$patient->loadRefDossierMedical();
$dossier_medical =& $patient->_ref_dossier_medical;
$dossier_medical->needsRead();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

if (CModule::getActive("maternite")) {
    $grossesses = $patient->loadRefsGrossesses();
    array_walk(
        $grossesses,
        function (CGrossesse $grossesse) {
            return $grossesse->loadRefsGrossessesAnt();
        }
    );
    $sejour->loadRefNaissance();
}

$date_sejour = $sejour->_id ? $sejour->entree_prevue : CMbDT::dateTime();
$where = array();
$where["patient_id"] = " = '$patient_id'";
$where[] = "sortie_prevue < '$date_sejour' OR sortie < '$date_sejour'";
$where["sejour.group_id"] = CSQLDataSource::prepareIn(array_keys(CGroups::loadGroups(PERM_READ)));
$_sejour = new CSejour();
/* @var CSejour[] $sejours*/
$sejours = $_sejour->loadList($where, 'entree DESC');
foreach ($sejours as $_sejour) {
  $_sejour->loadRefsOperations();

  if (!$_sejour->_motif_complet || $_sejour->annule) {
    unset($sejours[$_sejour->_id]);
    continue;
  }
}

$prescription_sejour = $sejour->loadRefPrescriptionSejour();
if ($prescription_sejour) {
  $prescription_sejour->countLinesTP();
}

$atcd_absence = array();

// Chargements des antecedents et traitements du dossier_medical
if ($dossier_medical->_id) {
  // On doit charger TOUS les antecedents, meme les annulés (argument true)
  $dossier_medical->loadRefsAntecedents(true, $sort_by_date);
  $dossier_medical->loadRefsTraitements(true);
  //absence atcd
  $atcd_absence = $dossier_medical->loadRefsAntecedents(true, $sort_by_date, false, false, 1);

  //atcd
  $dossier_medical->loadRefsAntecedents(true, $sort_by_date, false, false, 0);
  $dossier_medical->loadRefsTraitements(true);
  $dossier_medical->countAntecedents();
  $dossier_medical->countTraitements();

  $prescription = $dossier_medical->loadRefPrescription();

  foreach ($dossier_medical->_all_antecedents as $_antecedent) {
    $_antecedent->updateOwnerAndDates();
    if ($sejour->_id) {
      $_antecedent->loadRefLinkedElements($sejour->_id);
    }
  }
  foreach ($atcd_absence as $_atcd_absence) {
    $_atcd_absence->updateOwnerAndDates();
    if ($sejour->_id) {
      $_atcd_absence->loadRefLinkedElements($sejour->_id);
    }
  }
  foreach ($dossier_medical->_ref_traitements as $_traitement) {
    $_traitement->updateOwnerAndDates();
  }

  if ($prescription && is_array($prescription->_ref_prescription_lines)) {
    foreach ($prescription->_ref_prescription_lines as $_line) {
      $_line->loadRefsPrises();
      if ($_line->fin && $_line->fin <= CMbDT::date()) {
        $_line->_stopped = true;
        $dossier_medical->_count_cancelled_traitements++;
      }
      else {
        $dossier_medical->_count_traitements++;
      }
    }
  }
}

// tri du tableau des antécédents en fonction de la preference comme le mode grille
$order_mode_grille    = CAppUI::pref("order_mode_grille");
$sort_type_antecedent = array();
$sort_date_antecedent = array();
$order_decode         = array();

if ($order_mode_grille != "") {
  $order_decode = get_object_vars(json_decode($order_mode_grille));

  if (is_array($order_decode)) {
    foreach ($order_decode as $key_type => $_decode) {
      if (!isset($dossier_medical->_ref_antecedents_by_type_appareil[$key_type])) {
        continue;
      }
      $sort_type_antecedent[$key_type] = $dossier_medical->_ref_antecedents_by_type_appareil[$key_type];

      foreach ($dossier_medical->_all_antecedents as $_antecedent) {
        if ($_antecedent->type == $key_type) {
          $sort_date_antecedent[$_antecedent->_id] = $_antecedent;
        }
      }
    }

    foreach ($order_decode as $key_type => $_decode) {
      foreach ($dossier_medical->_all_antecedents as $_antecedent) {
        if ($_antecedent->type != $key_type) {
          $sort_date_antecedent[$_antecedent->_id] = $_antecedent;
        }
      }
    }
  }

  // Par type
  $dossier_medical->_ref_antecedents_by_type_appareil = array_merge($sort_type_antecedent, $dossier_medical->_ref_antecedents_by_type_appareil);

  // Par date
  $dossier_medical->_all_antecedents = $sort_date_antecedent;
}
else {
  // tri du tableau des antécédents par appareil
  ksort($dossier_medical->_ref_antecedents_by_type_appareil);

  // tri du tableau des antécédents par type
  foreach ($dossier_medical->_ref_antecedents_by_type_appareil as $key_type => $antecedents_by_appareil) {
    ksort($dossier_medical->_ref_antecedents_by_type_appareil[$key_type]);
  }
}

$user = CAppUI::$user;
$user->isPraticien();

$hide_diff_func_atcd = CAppUI::pref("hide_diff_func_atcd");
// compte les atcds
$count = array(
  "atcd"         => 0,
  "abs"          => 0,
  "atcd_hidden"  => 0,
);
$all_antecedents   = array(
  "by_type" => $dossier_medical->_ref_antecedents_by_type_appareil,
  "by_type_absence" => $dossier_medical->_ref_antecedents_by_type_appareil_absence,
  "_all_antecedents"  => $dossier_medical->_all_antecedents,
  "atcd_absence"      => $atcd_absence
);
foreach ($all_antecedents as $key_mode_abs => $_groupe_antecedents) {
  if ($_groupe_antecedents && count($_groupe_antecedents)) {
    if (in_array($key_mode_abs, array("by_type", "by_type_absence"))) {
      //Tri par type
      $mode_abs = "_ref_antecedents_by_type_appareil" . (($key_mode_abs == "by_type") ? "" : "_absence");
      foreach ($_groupe_antecedents as $key_type => $antecedents_by_appareil) {
        if (!$antecedents_by_appareil || !is_array($antecedents_by_appareil)) {
          continue;
        }
        foreach ($antecedents_by_appareil as $_key_antecedents => $_antecedents) {
          /** @var CAntecedent $_antecedent */
          foreach ($_antecedents as $_key_antecedent => $_antecedent) {
            $count[($key_mode_abs == "by_type" && $_antecedent->absence != 1) ? "atcd" : "abs"]++;
            $owner = $_antecedent->loadRefOwner();
            if ($owner->function_id != $user->function_id) {
              $count["atcd_hidden"]++;
              if ($hide_diff_func_atcd) {
                unset($dossier_medical->{$mode_abs}[$key_type][$_key_antecedents][$_key_antecedent]);
              }
            }
          }
        }
      }
    }
    elseif ($hide_diff_func_atcd) {
      //Tri par date
      /** @var CAntecedent $_antecedent */
      foreach ($_groupe_antecedents as $_key_antecedent => $_antecedent) {
        $owner = $_antecedent->loadRefOwner();
        if ($owner->function_id != $user->function_id) {
          if ($key_mode_abs == "atcd_absence") {
            unset($atcd_absence[$_key_antecedent]);
          }
          else {
            unset($dossier_medical->{$key_mode_abs}[$_key_antecedent]);
          }
        }
      }
    }
  }
}

$count_abs_allergie = 0;
foreach ($atcd_absence as $_atcd_absence) {
  if ($_atcd_absence->type == 'alle') {
    $count_abs_allergie++;
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("sejours"           , $sejours);
$smarty->assign("sejour"            , $sejour);
$smarty->assign("patient"           , $patient);
$smarty->assign("_is_anesth"        , $_is_anesth);
$smarty->assign("user"              , $user);
$smarty->assign("sort_by_date"      , $sort_by_date);
$smarty->assign("type_see"          , $type_see);
$smarty->assign("dossier_anesth_id" , $dossier_anesth_id);
$smarty->assign("show_gestion_tp"   , $show_gestion_tp);
$smarty->assign("context_date_min"  , $context_date_min);
$smarty->assign("context_date_max"  , $context_date_max);
$smarty->assign("atcd_absence"      , $atcd_absence);
$smarty->assign("count_abs_allergie", $count_abs_allergie);
$smarty->assign("count_atcd"        , $count["atcd"]);
$smarty->assign("count_atcd_hidden" , $count["atcd_hidden"]);
$smarty->assign("count_abs"         , $count["abs"]);
$smarty->display("inc_list_ant");
