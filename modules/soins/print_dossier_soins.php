<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Bloc\CPosteSSPI;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Medicament\CMedicamentProduit;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\MonitoringPatient\CSupervisionTimeline;
use Ox\Mediboard\MonitoringPatient\SupervisionGraph;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\PatientMonitoring\CMonitoringConcentrator;
use Ox\Mediboard\PatientMonitoring\CMonitoringSession;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\Services\ConstantesService;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\SalleOp\CDailyCheckItem;
use Ox\Mediboard\SalleOp\CDailyCheckList;

// Pas de migration en CView pour les exports
$sejour_id         = CValue::get("sejour_id");
$offline           = CValue::get("offline");
$in_modal          = CValue::get("in_modal");
$embed             = CValue::get("embed");
$period            = CValue::get("period");
$forms_limit       = CValue::get("forms_limit");
$date_min          = CValue::get("entree");
$date_max          = CValue::get("sortie");
$show_forms        = CValue::get("show_forms");
$operation_id      = CValue::get("operation_id");
$checkbox_selected = CValue::get("checkbox_selected", "");

if($checkbox_selected == "") {
    $checkbox_selected = [];
} else {
    $checkbox_selected = json_decode(stripslashes($checkbox_selected));
}

if (!$sejour_id) {
  CAppUI::stepMessage(UI_MSG_WARNING, "Veuillez sélectionner un sejour pour visualiser le dossier complet");

  return;
}

$blood_sugar_reports = [];
$fiches_anesthesies  = [];
$formulaires         = null;

global $atc_classes;
$atc_classes = array();

if ($date_min && $date_max && $date_min > $date_max) {
  $dates    = array($date_min, $date_max);
  $date_min = $dates[1];
  $date_max = $dates[0];
}

if ($period) {
  $date_min = CMbDT::dateTime("- $period HOURS");
  $date_max = null;
}

// Chargement du sejour
$sejour = new CSejour();
$sejour->load($sejour_id);

if (!$offline) {
  CAccessMedicalData::logAccess($sejour);
}

$sejour->loadNDA();
$sejour->loadExtDiagnostics();
$sejour->loadRefsConsultAnesth();
$sejour->_ref_consult_anesth->loadRefConsultation();
$sejour->canRead();

$sejour->loadRefsExamsIGS();
$sejour->loadRefsChungScore();
$sejour->loadRefsActesCCAM();

// Chargement des affectations
$sejour->loadRefCurrAffectation()->loadRefLit();
$where_aff = [];
if($date_min && $date_max) {
    $where_aff["entree"] = "BETWEEN '$date_min' AND '$date_max'";
    $where_aff["sortie"] = "BETWEEN '$date_min' AND '$date_max'";
} elseif ($date_min) {
    $where_aff["entree"] = ">= '$date_min'";
    $where_aff["sortie"] = ">= '$date_min'";
} elseif ($date_max) {
    $where_aff["sortie"] = "<= '$date_max'";
    $where_aff["entree"] = "<= '$date_max'";
}
$affectations = $sejour->loadRefsAffectations("sortie DESC", $where_aff);
CAffectation::massUpdateView($affectations);

// Chargement des tâches
foreach ($sejour->loadRefsTasks($date_min, $date_max) as $_task) {
  $_task->loadRefPrescriptionLineElement();
  $_task->setDateAndAuthor();
  $_task->loadRefAuthor();
  $_task->loadRefAuthorRealise();
}

// Chargement des opérations
$where_op = [];
if ($date_min && $date_max) {
    $where_op["date"] = "BETWEEN '$date_min' AND '$date_max'";
} elseif ($date_min) {
    $where_op["date"] = ">= '$date_min'";
} elseif ($date_max) {
    $where_op["date"] = "<= '$date_max'";
}
$sejour->loadRefsOperations($where_op);
$checklists = CStoredObject::massLoadBackRefs($sejour->_ref_operations, "check_lists", "date, date_validate");
CStoredObject::massLoadBackRefs($checklists, "items");
foreach ($sejour->_ref_operations as $_interv) {
  $_interv->loadRefChirs();
  $_interv->loadRefAnesth();
  $_interv->loadRefPlageOp();
  $_interv->_ref_chir->loadRefFunction();
  $_interv->loadRefsConsultAnesth();
  $_interv->loadRefBrancardage();

  /** @var CDailyCheckList[] $check_lists */
  $check_lists = $_interv->loadBackRefs("check_lists", "date, date_validate");

  if (is_array($check_lists)) {
    foreach ($check_lists as $_check_list_id => $_check_list) {
      // Remove check lists not signed
      if (!$_check_list->validator_id) {
        unset($_interv->_back["check_lists"][$_check_list_id]);
        unset($check_lists[$_check_list_id]);
        continue;
      }

      $_check_list->loadItemTypes();
      $_check_list->loadBackRefs('items');
      /** @var $_item CDailyCheckItem */
      foreach ($_check_list->_back['items'] as $_item) {
        $_item->loadRefsFwd();
      }
    }
  }

  $params = array(
    "dossier_anesth_id" => $_interv->_ref_consult_anesth->_id,
    "operation_id"      => $_interv->_id,
    "offline"           => 1,
    "print"             => 1,
    "pdf"               => 0
  );

  $fiches_anesthesies[$_interv->_id] = CApp::fetch("dPcabinet", "print_fiche", $params);
}

if ($embed) {
  // Fichiers et documents du sejour
  $sejour->loadRefsDocItems(false);

  // Fichiers et documents des interventions
  $interventions = $sejour->_ref_operations;
  foreach ($interventions as $_interv) {
    $_interv->loadRefPlageOp();
    $_interv->loadRefsDocItems(false);
  }

  // Fichiers et documents des consultations
  $consultations = $sejour->loadRefsConsultations();
  foreach ($consultations as $_consult) {
    $_consult->loadRefsDocItems(false);
  }

  $sejour->_ref_consult_anesth->_ref_consultation->loadRefsDocItems(false);
  $sejour->_ref_consult_anesth->loadRefsDocItems(false);
}

// Chargement du patient
$patient = $sejour->loadRefPatient();
$patient->loadComplete();
$patient->loadIPP();
$patient_insnir = $patient->loadRefPatientINSNIR();
$patient_insnir->createDatamatrix($patient_insnir->createDataForDatamatrix());

// Chargement du dossier medical
$dossier_medical = $patient->_ref_dossier_medical;
$dossier_medical->canRead();
$dossier_medical->countAntecedents();

$dossier    = array();
$list_lines = array();

if (CModule::getActive("dPprescription")) {
  // Chargement du dossier de soins cloturé
  $prescription                = $sejour->loadRefPrescriptionSejour();
  $prescription->_display_type = "atc";

  // Chargement des lignes
  CPrescription::$show_relais = true;
  $prescription->loadRefsLinesMedComments("1", "1", "1", "", "", "0", "1");
  $prescription->loadRefsLinesElementsComments("1");
  $prescription->loadRefsPrescriptionLineMixes("", "1", "1");
  $prescription->loadRefsLinesInscriptions();
  CPrescription::$show_relais = false;

    foreach ($prescription->_ref_lines_med_comments["med"] as $_atc => $_lines) {
        foreach ($_lines as $_id => $_line) {
            if ($date_min && $_line->_debut_reel && $_line->_debut_reel < $date_min) {
                unset($prescription->_ref_lines_med_comments["med"][$_atc][$_id]);
                continue;
            }
            if ($date_max && $_line->_fin_reelle && $_line->_fin_reelle > $date_max) {
                unset($prescription->_ref_lines_med_comments["med"][$_atc][$_id]);
                continue;
            }
        }
    }
    foreach ($prescription->_ref_lines_med_comments["comment"] as $_id => $_line) {
        if ($date_min && $_line->debut && $_line->debut < $date_min) {
            unset($prescription->_ref_lines_med_comments[$_id]);
            continue;
        }
        if ($date_max && $_line->fin && $_line->fin > $date_max) {
            unset($prescription->_ref_lines_med_comments[$_id]);
            continue;
        }
    }

    foreach ($prescription->_ref_prescription_line_mixes as $_id => $_line) {
        if ($date_min && $_line->_debut_reel && $_line->_debut_reel < $date_min) {
            unset($prescription->_ref_prescription_line_mixes[$_id]);
            continue;
        }
        if ($date_max && $_line->_fin_reelle && $_line->_fin_reelle > $date_max) {
            unset($prescription->_ref_prescription_line_mixes[$_id]);
            continue;
        }
    }

    foreach ($prescription->_ref_lines_elements_comments as $_chap => $_lines_chap) {
        foreach ($_lines_chap as $_cat => $_lines_cat) {
            foreach ($_lines_cat as $_type => $_lines_type) {
                foreach ($_lines_type as $_id => $_line) {
                    $debut = "debut";
                    $fin   = "fin";
                    if ($_line->_class == "CPrescriptionLineElement") {
                        $debut = "_debut_reel";
                        $fin   = "_fin_reelle";
                    }
                    if ($date_min && $_line->$debut && $_line->$debut < $date_min) {
                        unset($prescription->_ref_lines_elements_comments[$_chap][$_cat][$_type][$_id]);
                        continue;
                    }
                    if ($date_max && $_line->$fin && $_line->$fin > $date_max) {
                        unset($prescription->_ref_lines_elements_comments[$_chap][$_cat][$_type][$_id]);
                        continue;
                    }
                }
            }
        }
    }

  $where                  = array();
  $where["planification"] = " = '0'";
  if ($date_min && $date_max) {
    $where[] = "dateTime BETWEEN '$date_min' AND '$date_max'";
  }
  elseif ($date_min) {
    $where["dateTime"] = " >= '$date_min'";
  }
  elseif ($date_max) {
    $where["dateTime"] = " <= '$date_max'";
  }

  $dmi_active = CModule::getActive("dmi");

  if (count($prescription->_ref_prescription_line_mixes)) {
    $lines_mixes_items = CStoredObject::massLoadBackRefs($prescription->_ref_prescription_line_mixes, "lines_mix", "solvant");
    $adms = CStoredObject::massLoadBackRefs($lines_mixes_items, "administrations", null, $where);
    CStoredObject::massLoadBackRefs($adms, "transmissions", null, array("cancellation_date IS NULL"));
    foreach ($prescription->_ref_prescription_line_mixes as $_prescription_line_mix) {
      $count_adm = 0;
      $_prescription_line_mix->loadRefsLines();
      foreach ($_prescription_line_mix->_ref_lines as $_perf_line) {
        $_perf_line->loadRefsAdministrations($where);
        $count_adm += count($_perf_line->_ref_administrations);
      }
      if (!$_prescription_line_mix->relai_actif && !$count_adm) {
        unset($prescription->_ref_prescription_line_mixes[$_perf_line->_id]);
        unset($prescription->_ref_prescription_line_mixes_by_type[$_prescription_line_mix->type_line == "injection_directe" ? "inj" : $_prescription_line_mix->type_line][$_prescription_line_mix->_id]);
        continue;
      }
      $_prescription_line_mix->calculQuantiteTotal();
      $_prescription_line_mix->loadRefPraticien();
      $_prescription_line_mix->loadRefsVariations();
      foreach ($_prescription_line_mix->_ref_lines as $_perf_line) {
        $list_lines[$_prescription_line_mix->type_line][$_perf_line->_id] = $_perf_line;
        foreach ($_perf_line->_ref_administrations as $_administration_perf) {
          $_administration_perf->loadRefAdministrateur();
          $_administration_perf->loadRefsTransmissions();
          $dossier[CMbDT::date($_administration_perf->dateTime)][$_prescription_line_mix->type_line][$_perf_line->_id][$_administration_perf->quantite][$_administration_perf->_id] = $_administration_perf;
        }
      }
    }
  }

  // Parcours des lignes de medicament et stockage du dossier cloturé
  if (count($prescription->_ref_lines_med_comments["med"])) {
    $adms = CStoredObject::massLoadBackRefs($prescription->_ref_prescription_lines, "administrations", null, $where);
    CStoredObject::massLoadBackRefs($adms, "transmissions", null, array("cancellation_date IS NULL"));
    foreach ($prescription->_ref_lines_med_comments["med"] as $_atc => $lines_by_type) {
      $add_line = true;
      /** @var CPrescriptionLineMedicament $_line_med */
      foreach ($lines_by_type as $med_id => $_line_med) {
        $_line_med->loadRefMomentArret();
        $_line_med->loadRefsAdministrations(null, $where);
        if (!$_line_med->relai_actif && !count($_line_med->_ref_administrations)) {
          unset($prescription->_ref_lines_med_comments["med"][$_atc][$_line_med->_id]);
          $add_line = false;
          continue;
        }
        $list_lines["med"][$_line_med->_id] = $_line_med;
        foreach ($_line_med->_ref_administrations as $_administration_med) {
          $_administration_med->loadRefAdministrateur();
          $_administration_med->loadRefsTransmissions();
          $dossier[CMbDT::date($_administration_med->dateTime)]["med"][$_line_med->_id][$_administration_med->quantite][$_administration_med->_id] = $_administration_med;
        }
      }

      if ($add_line && !isset($atc_classes[$_atc])) {
        $medicament_produit = new CMedicamentProduit();
        $atc_classes[$_atc] = $medicament_produit->getLibelleATC($_atc);
      }
    }
  }

  // Parcours des lignes d'elements
  if (count($prescription->_ref_lines_elements_comments)) {
    $adms = CStoredObject::massLoadBackRefs($prescription->_ref_prescription_lines_element, "administrations", null, $where);
    CStoredObject::massLoadBackRefs($adms, "transmissions", null, array("cancellation_date IS NULL"));
    foreach ($prescription->_ref_lines_elements_comments as $chap => $_lines_by_chap) {
      foreach ($_lines_by_chap as $_lines_by_cat) {
        foreach ($_lines_by_cat["comment"] as $_line_elt_comment) {
          $_line_elt_comment->loadRefPraticien();
        }
        /** @var CPrescriptionLineElement $_line_elt */
        foreach ($_lines_by_cat["element"] as $_line_elt) {
          if (!$_line_elt->relai_actif && !count($_line_elt->_ref_administrations)) {
            unset($prescription->_ref_lines_elements_comments[$chap][$_line_elt->_id]);
            continue;
          }
          $list_lines[$chap][$_line_elt->_id] = $_line_elt;
          $_line_elt->loadRefsAdministrations(null, $where);
          foreach ($_line_elt->_ref_administrations as $_administration_elt) {
            $_administration_elt->loadRefAdministrateur();
            $_administration_elt->loadRefsTransmissions();
            $_administration_elt->loadRefsExamenNouveauNe();
            $dossier[CMbDT::date($_administration_elt->dateTime)][$chap][$_line_elt->_id][$_administration_elt->quantite][$_administration_elt->_id] = $_administration_elt;
          }

          if ($_line_elt->_chapitre == "dm" && $dmi_active) {
            $adms_dm = $_line_elt->loadRefsAdministrationsDM();
            CStoredObject::massLoadFwdRef($adms_dm, "product_id");
            CStoredObject::massLoadFwdRef($adms_dm, "order_item_reception_id");
            foreach ($adms_dm as $_adm_dm) {
              $_adm_dm->loadRefProduct();
              $_adm_dm->loadRefPraticien();
              $_adm_dm->loadRefProductOrderItemReception()->loadRefOrderItem()->loadReference()->loadRefSociete();
            }
          }
        }
      }
    }
  }

  foreach ($prescription->_ref_lines_inscriptions as $inscriptions_by_type) {
    foreach ($inscriptions_by_type as $_inscription) {
      switch ($_inscription->_class) {
        case "CPrescriptionLineMix":
          foreach ($_inscription->loadRefsLines() as $_mix_item) {
            $_mix_item->loadRefsAdministrations($where);
            foreach ($_mix_item->_ref_administrations as $_adm_inscription) {
              $_adm_inscription->loadRefAdministrateur();
              $chapitre                                                                                                                           = "perfusion";
              $list_lines[$chapitre][$_mix_item->_id]                                                                                             = $_mix_item;
              $dossier[CMbDT::date($_adm_inscription->dateTime)][$chapitre][$_mix_item->_id][$_adm_inscription->quantite][$_adm_inscription->_id] = $_adm_inscription;
            }
          }
          break;
        default:
          $_inscription->loadRefsAdministrations(null, $where);
          foreach ($_inscription->_ref_administrations as $_adm_inscription) {
            $_adm_inscription->loadRefAdministrateur();
            if ($_inscription instanceof CPrescriptionLineMedicament) {
              $chapitre = "med";
            }
            else {
              $chapitre = $_inscription->_chapitre;
            }
            $list_lines[$chapitre][$_inscription->_id]                                                                                             = $_inscription;
            $dossier[CMbDT::date($_adm_inscription->dateTime)][$chapitre][$_inscription->_id][$_adm_inscription->quantite][$_adm_inscription->_id] = $_adm_inscription;
          }
      }
    }
  }

  if ($dmi_active) {
    foreach ($prescription->loadRefsLinesDMI() as $_line_dmi) {
      $_line_dmi->loadRefProduct();
      $_line_dmi->loadRefPraticien();
      $_line_dmi->loadRefProductOrderItemReception()->loadRefOrderItem()->loadReference()->loadRefSociete();
      //$_line_dmi->_barcode_image = CBarcodeParser::makeBarcode($_line_dmi->_ref_product_order_item_reception->code);
    }
  }

    // Make blood sugar report
    $activate_choice_blood_glucose_units = CConstantesMedicales::getHostConfig(
        "activate_choice_blood_glucose_units",
        CConstantesMedicales::guessHost($sejour)
    );

    $constantes_service = new ConstantesService();


    $blood_sugar_reports = $constantes_service
        ->betweenDates(
            $date_min ? DateTime::createFromFormat("Y-m-d H:i:s", $date_min) : null,
            $date_max ? DateTime::createFromFormat("Y-m-d H:i:s", $date_max) : null
        )
        ->withChooseUnit($activate_choice_blood_glucose_units)
        ->getBloodSugarReport($sejour);
}

ksort($dossier);

// L'appel à print_fiche écrase les interventions du dossier d'anesthésie.
// Il faut charger le suivi médical à posteriori
$cibles           = [];
$last_trans_cible = [];
$users            = [];
$functions        = [];
$sejour->loadSuiviMedical($date_min, null, $cibles, $last_trans_cible, null, $users, null, $functions, 1, $date_max);

if ($offline && CModule::getActive("forms")) {
  $params = array(
    "detail"          => 3,
    "reference_id"    => $sejour->_id,
    "reference_class" => $sejour->_class,
    "target_element"  => "ex-objects-$sejour->_id",
    "print"           => 1,
    "limit"           => $forms_limit,
    "keep_session"    => 1,
    "date_time_min"    => $date_min,
    "date_time_max"    => $date_max,
  );

  // If error while fetching forms log and don't display them
  try {
    $formulaires = CApp::fetch("forms", "ajax_list_ex_object", $params);
  }
  catch (Throwable $e) {
    $formulaires = null;
    CApp::log($e->getMessage(), $e, LoggerLevels::LEVEL_WARNING);
  }
}

// Constantes du séjour
$where = array();
if ($date_min && $date_max) {
  $where[] = "datetime BETWEEN '$date_min' AND '$date_max'";
}
elseif ($date_min) {
  $where["datetime"] = " >= '$date_min'";
}
elseif ($date_max) {
  $where["datetime"] = " <= '$date_max'";
}
$sejour->loadListConstantesMedicales($where);

foreach ($sejour->_list_constantes_medicales as $_constante) {
  $_constante->loadRefsComments();
}

/* Les constantes sont divisées en section de 10 pour ne pas géner l'impression */
$constants_chunks = array_chunk($sejour->_list_constantes_medicales, 10, true);
$constants_grids = array();
foreach ($constants_chunks as $constant_chunk) {
  $constants_grids[] = CConstantesMedicales::buildGrid($constant_chunk, false, true, CConstantesMedicales::guessHost($sejour));
}

$praticien = new CMediusers();

$filter_date         = new CSejour();
$filter_date->entree = $date_min;
$filter_date->sortie = $date_max;

// Nécessaire pour la feuille de bloc
$operation = COperation::findOrNew($operation_id);

$surveillance_data = array();
$pack              = null;
$concentrators     = null;
$all_concentrators = null;
$session           = null;

if ($operation->_id && count($sejour->_ref_operations) > 0) {
  $operation->loadRefSejour()->loadRefPatient();
  $operation->loadRefsConsultAnesth();
  $operation->loadRefPlageOp();
  $operation->loadExtCodesCCAM();
  $operation->loadRefChir()->loadRefFunction();
  $operation->loadRefsActesCCAM();

  foreach ($operation->_ref_actes_ccam as $acte) {
    $acte->loadRefsFwd();
  }
  $operation->loadAffectationsPersonnel();
  $operation->guessActesAssociation();

  $operation->loadRefSortieLocker()->loadRefFunction();

  $operation->loadRefsMaterielsOperatoires();

  foreach ($operation->_refs_materiels_operatoires as $_materiel_operatoire) {
    $_materiel_operatoire->loadRelatedProduct();
    $_materiel_operatoire->loadRefsConsommations();
  }

  if (CAppUI::gconf('dPsalleOp timings use_garrot') && CAppUI::gconf('dPsalleOp COperation garrots_multiples')) {
    $operation->loadGarrots();
  }

//Chargement des checklist validées à imprimer
  /** @var CDailyCheckList[] $check_lists */
  $check_lists = $operation->loadBackRefs("check_lists", "date");
  foreach ($check_lists as $_check_list_id => $_check_list) {
    // Remove check lists not signed
    if (!$_check_list->validator_id) {
      unset($operation->_back["check_lists"][$_check_list_id]);
      unset($check_lists[$_check_list_id]);
      continue;
    }
    $_check_list->loadItemTypes();
    $_check_list->loadRefListType();
    $_check_list->loadBackRefs('items', "daily_check_item_id");
    foreach ($_check_list->_back['items'] as $_item) {
      /* @var CDailyCheckItem $_item*/
      $_item->loadRefsFwd();
    }
  }

// Graphique de surveillance perop
  $where_anesth_preop = $where_anesth_perop = $where_anesth_sspi = array();
  $concentrators = $all_concentrators = $session = null;

  if ($offline && CModule::getActive("monitoringBloc") && CAppUI::gconf("monitoringBloc general active_graph_supervision")) {
    $salle = $operation->loadRefSalle();
    $bloc = $salle->loadRefBloc();

// Preop
    if ($operation->graph_pack_preop_id) {
      [$perop_datetime_min, $perop_datetime_max] = SupervisionGraph::getTimingsByType($operation, "preop");

      if (!CAppUI::pref('show_all_datas_surveillance_timeline')) {
        $where_anesth_preop["datetime"] = "BETWEEN '$perop_datetime_min' AND '$perop_datetime_max'";
      }

      $operation->loadRefsAnesthPerops($where_anesth_preop);

      $pack = $operation->loadRefGraphPackPreop();

      [
        $preop_graphs, $preop_yaxes_count,
        $preop_time_min, $preop_time_max,
        $preop_time_debut_op_iso, $preop_time_fin_op_iso,
        $preop_evenement_groups, $preop_evenement_items, $preop_timeline_options, $display_current_time
        ] = CSupervisionTimeline::makeTimeline($operation, $pack, true, "preop", null, null, true);

      $preop_time_debut_op = CMbDT::toTimestamp($preop_time_debut_op_iso);
      $preop_time_fin_op   = CMbDT::toTimestamp($preop_time_fin_op_iso);

      $surveillance_data["preop"] = array(
        "graphs"               => $preop_graphs,
        "yaxes_count"          => $preop_yaxes_count,
        "time_min"             => $preop_time_min,
        "time_max"             => $preop_time_max,
        "time_debut_op"        => $preop_time_debut_op,
        "time_fin_op"          => $preop_time_fin_op,
        "timeline_options"     => $preop_timeline_options,
        "display_current_time" => $display_current_time,
      );
    }

// Perop
    if ($operation->graph_pack_id) {
      [$perop_datetime_min, $perop_datetime_max] = SupervisionGraph::getTimingsByType($operation, "perop");

      if (!CAppUI::pref('show_all_datas_surveillance_timeline')) {
        $where_anesth_perop["datetime"] = "BETWEEN '$perop_datetime_min' AND '$perop_datetime_max'";
      }

      $operation->loadRefsAnesthPerops($where_anesth_perop);

      $pack = $operation->loadRefGraphPack();

      [
        $perop_graphs, $perop_yaxes_count,
        $perop_time_min, $perop_time_max,
        $perop_time_debut_op_iso, $perop_time_fin_op_iso,
        $perop_evenement_groups, $perop_evenement_items, $perop_timeline_options, $display_current_time
        ] = CSupervisionTimeline::makeTimeline($operation, $pack, true, "perop", null, null, true);

      $perop_time_debut_op = CMbDT::toTimestamp($perop_time_debut_op_iso);
      $perop_time_fin_op   = CMbDT::toTimestamp($perop_time_fin_op_iso);

      $surveillance_data["perop"] = array(
        "graphs"               => $perop_graphs,
        "yaxes_count"          => $perop_yaxes_count,
        "time_min"             => $perop_time_min,
        "time_max"             => $perop_time_max,
        "time_debut_op"        => $perop_time_debut_op,
        "time_fin_op"          => $perop_time_fin_op,
        "timeline_options"     => $perop_timeline_options,
        "display_current_time" => $display_current_time,
      );
    }

// SSPI
    if ($operation->graph_pack_sspi_id) {
      [$sspi_datetime_min, $sspi_datetime_max] = SupervisionGraph::getTimingsByType($operation, "sspi");

      if (!CAppUI::pref('show_all_datas_surveillance_timeline')) {
        $where_anesth_sspi["datetime"] = "BETWEEN '$sspi_datetime_min' AND '$sspi_datetime_max'";
      }

      $operation->loadRefsAnesthPerops($where_anesth_sspi);

      $pack = $operation->loadRefGraphPackSSPI();

      [
        $sspi_graphs, $sspi_yaxes_count,
        $sspi_time_min, $sspi_time_max,
        $sspi_time_debut_op_iso, $sspi_time_fin_op_iso,
        $sspi_evenement_groups, $sspi_evenement_items, $sspi_timeline_options, $display_current_time
        ] = CSupervisionTimeline::makeTimeline($operation, $pack, true, "sspi", null, null, true);

      $sspi_time_debut_op = CMbDT::toTimestamp($sspi_time_debut_op_iso);
      $sspi_time_fin_op   = CMbDT::toTimestamp($sspi_time_fin_op_iso);

      $surveillance_data["sspi"] = array(
        "graphs"               => $sspi_graphs,
        "yaxes_count"          => $sspi_yaxes_count,
        "time_min"             => $sspi_time_min,
        "time_max"             => $sspi_time_max,
        "time_debut_op"        => $sspi_time_debut_op,
        "time_fin_op"          => $sspi_time_fin_op,
        "timeline_options"     => $sspi_timeline_options,
        "display_current_time" => $display_current_time,
      );
    }

    if ($pack) {
      $pack->getTimingValues($operation);
    }

    /* Initialize the data for the concentrator */
    if (CModule::getActive("patientMonitoring")) {
      $postes = array();

      $poste_load = new CPosteSSPI();
      $postes = $poste_load->loadMatchingListEsc();

      $where_concentrator           = array();
      $where_concentrator["active"] = " = '1'";

      $concentrators = CStoredObject::massLoadBackRefs($postes, "monitoring_concentrators", null, $where_concentrator);
      $all_concentrators = CMonitoringConcentrator::getForBloc($bloc);
      $session = CMonitoringSession::getCurrentSession($operation);
      $operation->_active_session = $session;
    }
  }
}

// Création du template
$smarty = new CSmartyDP("modules/soins");

$smarty->assign("filter_date", $filter_date);
$smarty->assign("sejour", $sejour);
$smarty->assign("dossier", $dossier);
$smarty->assign("list_lines", $list_lines);
$smarty->assign("constantes_medicales_grids", $constants_grids);

if (CModule::getActive("dPprescription")) {
  $smarty->assign("prescription", $prescription);
}

$smarty->assign("formulaires", $formulaires);
$smarty->assign("praticien", $praticien);
$smarty->assign("offline", $offline);
$smarty->assign("embed", $embed);
$smarty->assign("in_modal", $in_modal);
$smarty->assign("fiches_anesthesies", $fiches_anesthesies);
$smarty->assign("atc_classes", $atc_classes);
$smarty->assign("show_forms", $show_forms);
$smarty->assign("operation", $operation);
$smarty->assign("perops", array());
$smarty->assign("supervision_data", array());
$smarty->assign("surveillance_data", $surveillance_data);
$smarty->assign("pack"             , $pack);
$smarty->assign("concentrators"    , $concentrators);
$smarty->assign("all_concentrators", $all_concentrators);
$smarty->assign("session"          , $session);
$smarty->assign("checkbox_selected", $checkbox_selected);

//glycemie
$smarty->assign('blood_sugar', $blood_sugar_reports);

$smarty->display("print_dossier_soins");
