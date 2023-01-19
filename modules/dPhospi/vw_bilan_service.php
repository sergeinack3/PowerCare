<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CObservationMedicale;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CTransmissionMedicale;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CCategoryPrescription;
use Ox\Mediboard\Prescription\CPrescription;

/**
 * Bilan par service
 */
CApp::setMemoryLimit("1024M");
CApp::setTimeLimit(240);

function getCurrentLit($sejour, $date, $hour, $service_id, &$lits) {
  $affectations = $sejour->_ref_affectations;
  $datetime     = "$date $hour:00:00";
  foreach ($affectations as $_affectation) {
    if ($_affectation->service_id != $service_id) {
      continue;
    }
    if ($datetime >= $_affectation->entree && $datetime <= $_affectation->sortie) {
      $lit = $_affectation->loadRefLit();
      $lit->loadRefChambre();
      $lits["$lit->_ref_chambre->nom $lit->_view"] = $lit;

      return $lit;
    }
  }

  return null;
}

CCanDo::check();

$token_cat     = CView::get("token_cat", "str");
$periode       = CView::get("periode", 'str');
$service_id    = CView::get("service_id", 'str', true);
$by_patient    = CView::get("by_patient", 'bool default|0');
$show_inactive = CView::get("show_inactive", "bool default|0");
$_present_only = CView::get("_present_only", "bool default|1");
$mode_urgences = CView::get("mode_urgences", "bool default|0");
$offline       = CView::get("offline", "bool default|0");
$date          = CView::get("date", "date default|now", true);
$do            = CView::get("do", 'bool');
$cat_group_id  = CView::get("cat_group_id", 'ref class|CPrescriptionCategoryGroup');

$group = CGroups::loadCurrent();

if ($offline) {
  $by_patient   = true;
  $do           = 1;
  $day_relative = CView::get('day_relative', 'num');
  if (!is_null($day_relative) && $day_relative >= 0) {
    $date =  CMbDT::date("+$day_relative days", $date);
    $dateTime_min = "$date 00:00:00";
    $dateTime_max = "$date 23:59:59";
  }
  else {
    $dateTime_min = CMbDT::dateTime(" - " . CAppUI::conf("soins bilan hour_before", $group) . " HOURS");
    $dateTime_max = CMbDT::dateTime(" + " . CAppUI::conf("soins bilan hour_after", $group) . " HOURS");
  }
}
else {
  $dateTime_min = CView::get("_dateTime_min", array("dateTime", "default" => "$date 00:00:00"), true);
  $dateTime_max = CView::get("_dateTime_max", array("dateTime", "default" => "$date 23:59:59"), true);
}

CView::checkin();
CView::enforceSlave();

$plan_soins_active = CModule::getActive("planSoins");

$categories = $plan_soins_active ? CCategoryPrescription::loadCategoriesByChap(null, "current", 1) : [];

$date_min = CMbDT::date($dateTime_min);
$date_max = CMbDT::date($dateTime_max);

if ($token_cat == "all") {
  $token_cat = "trans|med|inj|perf|aerosol";

  foreach ($categories as $categories_by_chap) {
    foreach ($categories_by_chap as $category_id => $_categorie) {
      $token_cat .= "|$category_id";
    }
  }
}

$elts = $cats = explode("|", $token_cat);

CMbArray::removeValue("med", $elts);
CMbArray::removeValue("perf", $elts);
CMbArray::removeValue("inj", $elts);
CMbArray::removeValue("trans", $elts);

$do_elements    = (count($elts) > 0);
$do_medicaments = (in_array("med", $cats));
$do_injections  = (in_array("inj", $cats));
$do_perfusions  = (in_array("perf", $cats));
$do_aerosols    = (in_array("aerosol", $cats));
$do_stupefiants = (in_array("stup", $cats));
$do_trans       = (in_array("trans", $cats));

// Filtres sur l'heure des prises
$time_min = CMbDT::time($dateTime_min, "00:00:00");
$time_max = CMbDT::time($dateTime_max, "23:59:59");

// Stockage des jours concernés par le chargement
$dates   = array();
$nb_days = CMbDT::daysRelative($date_min, $date_max);
for ($i = 0; $i <= $nb_days; $i++) {
  $dates[] = CMbDT::date("+ $i DAYS", $date_min);
}

$sejours          = array();
$lits             = array();
$trans_and_obs    = array();
$list_lines       = array();
$lines_by_patient = array();

if ($do) {
  $sejour                 = new CSejour();
  $where                  = array();
  $ljoin                  = array();
  $order_by               = null;
  $where["sejour.entree"] = "<= '$dateTime_max'";
  $where["sejour.sortie"] = " >= '$dateTime_min'";

  if ($mode_urgences) {
    $where["sejour.type"]     = CSQLDataSource::prepareIn(CSejour::getTypesSejoursUrgence());
    $where["sejour.group_id"] = "= '$group->_id'";
  }
  else {
    if ($service_id == "NP") {
      $ljoin["affectation"]                = "sejour.sejour_id = affectation.sejour_id";
      $where["affectation.affectation_id"] = "IS NULL";
      $where["sejour.group_id"]            = "= '$group->_id'";
    }
    else {
      $ljoin["affectation"] = "sejour.sejour_id = affectation.sejour_id";

      $where["affectation.entree"]     = "<= '$dateTime_max'";
      $where["affectation.sortie"]     = ">= '$dateTime_min'";
      $where["affectation.service_id"] = " = '$service_id'";
    }
  }

  if ($_present_only) {
    $where["sejour.sortie_reelle"] = 'IS NULL';
  }

  /** @var CSejour[] $sejours */
  $sejours = $sejour->loadList($where, $order_by, null, "sejour.sejour_id", $ljoin);
  CStoredObject::massLoadFwdRef($sejours, "patient_id");
  CStoredObject::massCountBackRefs($sejours, "operations");
  $affectations = CStoredObject::massLoadBackRefs($sejours, 'affectations', 'sortie DESC');
  CAffectation::massUpdateView($affectations);
  foreach ($sejours as $_sejour) {
    $_sejour->loadRefPatient()->loadRefLatestConstantes();
    $_sejour->loadRefsOperations();
    $last_op = $_sejour->_ref_last_operation;

    $last_op->loadRefPlageOp();
    $last_op->loadRefChir();
    $last_op->loadExtCodesCCAM();

    $_sejour->loadRefsAffectations();
  }

  $sorter_affectation = CMbArray::pluck($sejours, "_ref_last_affectation", "_view");
  $sorter_patient     = CMbArray::pluck($sejours, "_ref_patient", "nom");

  array_multisort(
    $sorter_affectation, SORT_ASC,
    $sorter_patient, SORT_ASC,
    $sejours
  );

  $sejours_ids = array();
  if (count($sejours)) {
    $sejours     = array_combine(CMbArray::pluck($sejours, "_id"), $sejours);
    $sejours_ids = array_keys($sejours);

    foreach ($sejours as $_sejour) {
      $trans_and_obs[$_sejour->patient_id] = array();
    }
  }

  if ($do_trans) {
    $trans                   = new CTransmissionMedicale();
    $whereTrans              = array();
    $whereTrans[]            = "(degre = 'high' AND (date_max IS NULL OR date_max >= '$dateTime_min')) OR (date >= '$dateTime_min' AND date <= '$dateTime_max')";
    $whereTrans["sejour_id"] = CSQLDataSource::prepareIn($sejours_ids);

    $transmissions = $trans->loadList($whereTrans);
    CStoredObject::massLoadFwdRef($transmissions, "user_id");
    CStoredObject::massLoadFwdRef($transmissions, "cible_id");

    foreach ($transmissions as $_trans) {
      if ($_trans->cancellation_date) {
        continue;
      }
      $_trans->loadRefUser();
      $_trans->loadTargetObject();
      $_trans->loadRefCible();
      if ($_trans->_ref_object) {
        $_trans->_ref_object->loadRefsFwd();
      }
      $_trans->_ref_sejour                                              = $sejours[$_trans->sejour_id];
      $trans_and_obs[$_trans->_ref_sejour->patient_id][$_trans->date][] = $_trans;
    }

    $obs                   = new CObservationMedicale();
    $whereObs              = array();
    $whereObs[]            = "(degre = 'high') OR (date >= '$dateTime_min' AND date <= '$dateTime_max')";
    $whereObs["sejour_id"] = CSQLDataSource::prepareIn(array_keys($sejours));

    $observations = $obs->loadList($whereObs);

    CStoredObject::massLoadFwdRef($observations, "user_id");

    foreach ($observations as $_obs) {
      if ($_obs->cancellation_date) {
        continue;
      }
      $_obs->loadRefUser();
      $_obs->loadTargetObject();
      if ($_obs->_ref_object) {
        $_obs->_ref_object->loadRefsFwd();
      }
      $_obs->_ref_sejour                                            = $sejours[$_obs->sejour_id];
      $trans_and_obs[$_obs->_ref_sejour->patient_id][$_obs->date][] = $_obs;
    }

    $cste                        = new CConstantesMedicales();
    $whereCstes                  = array();
    $whereCstes[]                = "(datetime >= '$dateTime_min ' AND datetime <= '$dateTime_max')";
    $whereCstes["context_class"] = " = 'CSejour'";
    $whereCstes["context_id"]    = CSQLDataSource::prepareIn(array_keys($sejours));

    $constantes = $cste->loadList($whereCstes);
    CStoredObject::massLoadFwdRef($constantes, "user_id");

    foreach ($constantes as $_constante) {
      $_constante->loadRefUser();
      $_constante->_ref_context                                        = $sejours[$_constante->context_id];
      $trans_and_obs[$_constante->patient_id][$_constante->datetime][] = $_constante;
    }

    // Tri des transmission, observations et constantes par date décroissante
    foreach ($trans_and_obs as &$_trans) {
      krsort($_trans, SORT_STRING);
    }
  }

  $prescription = new CPrescription();

  if ($plan_soins_active && ($do_medicaments || $do_injections || $do_perfusions || $do_aerosols || $do_elements || $do_stupefiants)) {
    $wherePresc                 = array();
    $wherePresc["object_class"] = " = 'CSejour'";
    $wherePresc["object_id"]    = CSQLDataSource::prepareIn(array_keys($sejours));

    $prescriptions = $prescription->loadList($wherePresc);

    foreach ($prescriptions as $_prescription) {
      CView::enforceSlave();

      CPrescription::addMutex($_prescription->_guid);

      $sejour                     = $sejours[$_prescription->object_id];
      $_prescription->_ref_object = $sejour;

      // Chargement des lignes
      $_prescription->loadRefsLinesMed("1", "1");
      if ($do_elements) {
        $_prescription->loadRefsLinesElementByCat("1", "1");

        foreach ($_prescription->_ref_prescription_lines_element as $_line_elt) {
          $_line_elt->loadRefsPrevRelais();
        }
      }
      if ($do_perfusions || $do_aerosols || $do_stupefiants) {
        $_prescription->loadRefsPrescriptionLineMixes(null, 1, 1);
      }

      // Calcul du plan de soin
      $_prescription->_force_no_regroupement = 1;
      $_prescription->calculPlanSoin($dates);

      CPrescription::releaseMutex($_prescription->_guid);

      if ($do_medicaments || $do_injections || $do_perfusions || $do_aerosols || $do_stupefiants) {
        if ($do_perfusions || $do_aerosols || $do_stupefiants) {
          // Parcours et stockage des prescription_line_mixes
          if ($_prescription->_ref_prescription_line_mixes_for_plan) {
            foreach ($_prescription->_ref_prescription_line_mixes_for_plan as $_prescription_line_mix) {
              if ($_prescription_line_mix->type_line == "aerosol" && !$do_aerosols && !$do_stupefiants) {
                continue;
              }
              if ($_prescription_line_mix->type_line == "perfusion" && !$do_perfusions && !$do_stupefiants) {
                continue;
              }
              if ($_prescription_line_mix->type_line == "oxygene") {
                continue;
              }

              $_prescription_line_mix->loadActiveDates();
              $list_lines[$_prescription_line_mix->_class][$_prescription_line_mix->_id] = $_prescription_line_mix;
              // Prises prevues
              if (is_array($_prescription_line_mix->_prises_prevues)) {
                foreach ($_prescription_line_mix->_prises_prevues as $_date => $_prises_prevues_by_hour) {
                  foreach ($_prises_prevues_by_hour as $_hour => $_prise_prevue) {
                    $dateTimePrise = "$_date $_hour:00:00";
                    if ($dateTimePrise < $dateTime_min || $dateTimePrise > $dateTime_max) {
                      continue;
                    }
                    if (!$mode_urgences) {
                      $lit = getCurrentLit($sejour, $_date, $_hour, $service_id, $lits);
                      if (!$lit) {
                        continue;
                      }
                    }

                    foreach ($_prescription_line_mix->_ref_lines as $_perf_line) {
                      if (!$_perf_line->stupefiant && $do_stupefiants) {
                        continue;
                      }

                      if ($mode_urgences) {
                        $key1 = $sejour->patient_id;
                        $key2 = $sejour->_id;
                        $key3 = "med";
                      }
                      else {
                        $key1 = $by_patient ? "$lit->_ref_chambre->nom $lit->_view" : "med";
                        $key2 = $by_patient ? $sejour->_id : "$lit->_ref_chambre->nom $lit->_view";
                        $key3 = $by_patient ? "med" : $sejour->_id;
                      }

                      $list_lines[$_perf_line->_class][$_perf_line->_id] = $_perf_line;

                      // Plusieurs prises pdt la meme heure
                      if (array_key_exists("real_hour", $_prescription_line_mix->_prises_prevues[$_date][$_hour])) {
                        $count_prises_by_hour = count($_prescription_line_mix->_prises_prevues[$_date][$_hour]["real_hour"]);
                        @$lines_by_patient[$key1][$key2][$key3][$_date][$_hour]['CPrescriptionLineMix'][$_prescription_line_mix->_id][$_perf_line->_id]["prevu"] = $_perf_line->_quantite_administration * $count_prises_by_hour;
                      }

                      if (array_key_exists("manual", $_prescription_line_mix->_prises_prevues[$_date][$_hour])) {
                        @$lines_by_patient[$key1][$key2][$key3][$_date][$_hour]['CPrescriptionLineMix'][$_prescription_line_mix->_id][$_perf_line->_id]["prevu"] = $_prescription_line_mix->_prises_prevues[$_date][$_hour]["manual"][$_perf_line->_id];
                      }
                    }
                  }
                }
              }
              // Administrations effectuees
              foreach ($_prescription_line_mix->_ref_lines as $_perf_line) {
                if (!$_perf_line->stupefiant && $do_stupefiants) {
                  continue;
                }
                $list_lines[$_perf_line->_class][$_perf_line->_id] = $_perf_line;
                if (is_array($_perf_line->_administrations)) {
                  foreach ($_perf_line->_administrations as $_date => $_adm_by_hour) {
                    foreach ($_adm_by_hour as $_hour => $_adm) {
                      $dateTimePrise = "$_date $_hour:00:00";
                      if ($dateTimePrise < $dateTime_min || $dateTimePrise > $dateTime_max) {
                        continue;
                      }
                      if (!$mode_urgences) {
                        $lit = getCurrentLit($sejour, $_date, $_hour, $service_id, $lits);
                        if (!$lit) {
                          continue;
                        }
                      }
                      if ($mode_urgences) {
                        $key1 = $sejour->patient_id;
                        $key2 = $sejour->_id;
                        $key3 = "med";
                      }
                      else {
                        $key1 = $by_patient ? "$lit->_ref_chambre->nom $lit->_view" : "med";
                        $key2 = $by_patient ? $sejour->_id : "$lit->_ref_chambre->nom $lit->_view";
                        $key3 = $by_patient ? "med" : $sejour->_id;
                      }
                      @$lines_by_patient[$key1][$key2][$key3][$_date][$_hour]['CPrescriptionLineMix'][$_prescription_line_mix->_id][$_perf_line->_id]["administre"] = $_adm;
                    }
                  }
                }
              }
            }
          }
        }

        // Parcours des medicament du plan de soin
        $medicaments = array();
        if ($do_medicaments || $do_stupefiants) {
          $medicaments["med"] = $_prescription->_ref_lines_med_for_plan;
        }
        if ($do_injections || $do_stupefiants) {
          $medicaments["inj"] = $_prescription->_ref_injections_for_plan;
        }

        if ($do_medicaments || $do_injections || $do_stupefiants) {
          foreach ($medicaments as $type_med => $_medicaments) {
            if ($_medicaments) {
              foreach ($_medicaments as $_code_ATC => &$_cat_ATC) {
                foreach ($_cat_ATC as &$_lines_by_unite) {
                  foreach ($_lines_by_unite as &$_line_med) {
                    if (!$_line_med->stupefiant && $do_stupefiants) {
                      continue;
                    }
                    $_line_med->loadActiveDates();
                    $list_lines[$_line_med->_class][$_line_med->_id] = $_line_med;
                    // Prises prevues
                    if (is_array($_line_med->_quantity_by_date)) {
                      foreach ($_line_med->_quantity_by_date as $unite_prise => &$prises_prevues_by_unite) {
                        foreach ($prises_prevues_by_unite as $_date => &$prises_prevues_by_date) {
                          if (@is_array($prises_prevues_by_date['quantites'])) {
                            foreach ($prises_prevues_by_date['quantites'] as $_hour => &$prise_prevue) {
                              $dateTimePrise = "$_date $_hour:00:00";
                              if ($dateTimePrise < $dateTime_min || $dateTimePrise > $dateTime_max) {
                                continue;
                              }
                              if (!$mode_urgences) {
                                $lit = getCurrentLit($sejour, $_date, $_hour, $service_id, $lits);
                                if (!$lit) {
                                  continue;
                                }
                              }

                              if ($prise_prevue["total"]) {
                                if ($mode_urgences) {
                                  $key1 = $sejour->patient_id;
                                  $key2 = $sejour->_id;
                                  $key3 = "med";
                                }
                                else {
                                  $key1 = $by_patient ? "$lit->_ref_chambre->nom $lit->_view" : "med";
                                  $key2 = $by_patient ? $sejour->_id : "$lit->_ref_chambre->nom $lit->_view";
                                  $key3 = $by_patient ? "med" : $sejour->_id;
                                }

                                @$lines_by_patient[$key1][$key2][$key3][$_date][$_hour][$_line_med->_class][$_line_med->_id]["prevu"] += $prise_prevue["total"];
                                $prise_prevue["total"] = 0;
                              }
                            }
                          }
                        }
                      }
                    }
                    // Administration effectuees
                    if (is_array($_line_med->_administrations)) {
                      foreach ($_line_med->_administrations as $unite_prise => &$administrations_by_unite) {
                        foreach ($administrations_by_unite as $_date => &$administrations_by_date) {
                          foreach ($administrations_by_date as $_hour => &$administrations_by_hour) {
                            if (is_numeric($_hour)) {
                              $dateTimePrise = "$_date $_hour:00:00";
                              if ($dateTimePrise < $dateTime_min || $dateTimePrise > $dateTime_max) {
                                continue;
                              }
                              if (!$mode_urgences) {
                                $lit = getCurrentLit($sejour, $_date, $_hour, $service_id, $lits);
                                if (!$lit) {
                                  continue;
                                }
                              }
                              $quantite = @$administrations_by_hour["quantite"];

                              if ($mode_urgences) {
                                $key1 = $sejour->patient_id;
                                $key2 = $sejour->_id;
                                $key3 = "med";
                              }
                              else {
                                $key1 = $by_patient ? "$lit->_ref_chambre->nom $lit->_view" : "med";
                                $key2 = $by_patient ? $sejour->_id : "$lit->_ref_chambre->nom $lit->_view";
                                $key3 = $by_patient ? "med" : $sejour->_id;
                              }
                              if ($quantite) {
                                @$lines_by_patient[$key1][$key2][$key3][$_date][$_hour][$_line_med->_class][$_line_med->_id]["administre"] += $quantite;
                                $administrations_by_hour["quantite"] = 0;
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
      // Parcours des elements du plan de soin

      if ($_prescription->_ref_lines_elt_for_plan) {
        foreach ($_prescription->_ref_lines_elt_for_plan as $name_chap => &$elements_chap) {
          foreach ($elements_chap as $name_cat => &$elements_cat) {
            if (!in_array($name_cat, $cats)) {
              continue;
            }
            foreach ($elements_cat as &$_element) {
              foreach ($_element as &$_line_elt) {
                $_line_elt->loadActiveDates();
                $list_lines[$_line_elt->_class][$_line_elt->_id] = $_line_elt;
                // Prises prevues
                if (is_array($_line_elt->_quantity_by_date)) {
                  foreach ($_line_elt->_quantity_by_date as $unite_prise => &$prises_prevues_by_unite) {
                    foreach ($prises_prevues_by_unite as $_date => &$prises_prevues_by_date) {
                      if (@is_array($prises_prevues_by_date['quantites'])) {
                        foreach ($prises_prevues_by_date['quantites'] as $_hour => &$prise_prevue) {
                          $dateTimePrise = "$_date $_hour:00:00";
                          if ($dateTimePrise < $dateTime_min || $dateTimePrise > $dateTime_max) {
                            continue;
                          }
                          if (!$mode_urgences) {
                            $lit = getCurrentLit($sejour, $_date, $_hour, $service_id, $lits);
                            if (!$lit) {
                              continue;
                            }
                          }

                          if ($prise_prevue["total"]) {
                            if ($mode_urgences) {
                              $key1 = $sejour->patient_id;
                              $key2 = $sejour->_id;
                              $key3 = $name_chap;
                            }
                            else {
                              $key1 = $by_patient ? "$lit->_ref_chambre->nom $lit->_view" : $name_chap;
                              $key2 = $by_patient ? $sejour->_id : "$lit->_ref_chambre->nom $lit->_view";
                              $key3 = $by_patient ? $name_chap : $sejour->_id;
                            }
                            @$lines_by_patient[$key1][$key2][$key3][$_date][$_hour][$_line_elt->_class][$_line_elt->_id]["prevu"] += $prise_prevue["total"];
                            $prise_prevue = 0;
                          }
                        }
                      }
                    }
                  }
                }
                // Administration effectuees
                if (is_array($_line_elt->_administrations)) {
                  foreach ($_line_elt->_administrations as $unite_prise => &$administrations_by_unite) {
                    foreach ($administrations_by_unite as $_date => &$administrations_by_date) {
                      foreach ($administrations_by_date as $_hour => &$administrations_by_hour) {
                        if (is_numeric($_hour)) {
                          $dateTimePrise = "$_date $_hour:00:00";
                          if ($dateTimePrise < $dateTime_min || $dateTimePrise > $dateTime_max) {
                            continue;
                          }
                          if (!$mode_urgences) {
                            $lit = getCurrentLit($sejour, $_date, $_hour, $service_id, $lits);
                            if (!$lit) {
                              continue;
                            }
                          }

                          $quantite = @$administrations_by_hour["quantite"];
                          if ($quantite) {
                            if ($mode_urgences) {
                              $key1 = $sejour->patient_id;
                              $key2 = $sejour->_id;
                              $key3 = $name_chap;
                            }
                            else {
                              $key1 = $by_patient ? "$lit->_ref_chambre->nom $lit->_view" : $name_chap;
                              $key2 = $by_patient ? $sejour->_id : "$lit->_ref_chambre->nom $lit->_view";
                              $key3 = $by_patient ? $name_chap : $sejour->_id;
                            }
                            @$lines_by_patient[$key1][$key2][$key3][$_date][$_hour][$_line_elt->_class][$_line_elt->_id]["administre"] += $quantite;
                            $administrations_by_hour["quantite"] = 0;
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }

  // Tri des lignes
  ksort($lines_by_patient, SORT_STRING);
  foreach ($lines_by_patient as $name_chap => &$lines_by_chap) {
    ksort($lines_by_chap, SORT_STRING);
    foreach ($lines_by_chap as $lit_view => &$lines_by_sejour) {
      ksort($lines_by_sejour);
      foreach ($lines_by_sejour as $sejour_id => &$lines_by_date) {
        ksort($lines_by_date);
        foreach ($lines_by_date as $date => &$lines_by_hours) {
          ksort($lines_by_hours);
        }
      }
    }
  }
}

if (!$offline) {
  // Initialisation des filtres
  $prescription                = new CPrescription();
  $prescription->_dateTime_min = $dateTime_min;
  $prescription->_dateTime_max = $dateTime_max;

  // Reconstruction du tokenField
  $token_cat = implode("|", $cats);

  // Chargement de tous les groupes de categories de prescription de l'etablissement courant
  $all_groups = array();
  $cat_groups = CGroups::loadCurrent()->loadBackRefs("packs_categorie_prescription", "libelle");

  foreach ($cat_groups as $_cat_group) {
    $_cat_group->loadRefsCategoryGroupItems();
    foreach ($_cat_group->_ref_category_group_items as $_item) {
      $all_groups[$_cat_group->_id][] = $_item->category_prescription_id ? $_item->category_prescription_id : $_item->type_produit;
    }
  }
}

// Chargement du service
$service = new CService();
$service->load($service_id);

$smarty = new CSmartyDP();

$smarty->assign("sejours", $sejours);
$smarty->assign("trans_and_obs", $trans_and_obs);
$smarty->assign("list_lines", $list_lines);
$smarty->assign("lines_by_patient", $lines_by_patient);
$smarty->assign("lits", $lits);

$smarty->assign("offline", $offline);
$smarty->assign("token_cat", $token_cat);
$smarty->assign("prescription", $prescription);
$smarty->assign("periode", $periode);
$smarty->assign("dateTime_min", $dateTime_min);
$smarty->assign("dateTime_max", $dateTime_max);
$smarty->assign("show_inactive", $show_inactive);
$smarty->assign("_present_only", $_present_only);
$smarty->assign("mode_urgences", $mode_urgences);
$smarty->assign("by_patient", $by_patient);
$smarty->assign("params", CConstantesMedicales::$list_constantes);
$smarty->assign("plan_soins_active", $plan_soins_active);

if (!$offline) {
  $smarty->assign("cats", $cats);
  $smarty->assign("categories", $categories);
  $smarty->assign("cat_groups", $cat_groups);
  $smarty->assign("all_groups", $all_groups);
  $smarty->assign("cat_group_id", $cat_group_id);
}

$smarty->assign("service", $service);
$smarty->assign("dates", $dates);

$smarty->display("vw_bilan_service");
