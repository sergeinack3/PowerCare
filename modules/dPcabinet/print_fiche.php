<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CClassMap;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CThumbnail;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Moebius\CMoebiusAPI;
use Ox\Mediboard\Moebius\CMoebiusXML;
use Ox\Mediboard\Moebius\CRisque;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLine;
use Ox\Mediboard\Mpm\CPrescriptionLineMix;

CCanDo::check();

if (!CModule::getCanDo('dPcabinet')->edit && !CModule::getCanDo('soins')->read) {
  CModule::getCanDo('dPcabinet')->denied();
}

$date  = CValue::getOrSession("date", CMbDT::date());
$print = CValue::getOrSession("print", false);
$today = CMbDT::date();

$dossier_anesth_id     = CValue::get("dossier_anesth_id");
$operation_id          = CValue::getOrSession("operation_id");
$create_dossier_anesth = CValue::get("create_dossier_anesth", 1);
$multi                 = CValue::get("multi");
$offline               = CValue::get("offline");
$display               = CValue::get("display");
$pdf                   = CValue::get("pdf", 1);
$auto_print            = CValue::get("auto_print");
$mod_ambu              = CValue::get("mod_ambu");

CAccessMedicalData::logAccess("COperation-$operation_id");

$lines        = array();
$lines_per_op = array();
$lines_tp     = array();

// Consultation courante
$dossier_anesth = new CConsultAnesth();
if (!$dossier_anesth_id && $operation_id) {
  $where                 = array();
  $where["operation_id"] = " = '$operation_id'";
  $dossier_anesth->loadObject($where);
}
else {
  $dossier_anesth->load($dossier_anesth_id);
}

if (CAppUI::gconf("dPsalleOp COperation category_document_pre_anesth") && $dossier_anesth->_id) {
  $operation = $dossier_anesth->loadRefOperation();
  $last_file = $operation->getLastFileAnesthesia();

  if ($last_file && count($last_file)) {
    $smarty = new CSmartyDP("modules/dPcabinet");
    $smarty->assign("last_file", $last_file);
    $smarty->assign("print", $print);
    $smarty->assign("operation", $operation);
    $smarty->display("inc_vw_last_file_anesthesia");

    return;
  }
}

if (!$dossier_anesth->_id) {
  $selOp = new COperation();
  $selOp->load($operation_id);
  $selOp->loadRefsFwd();
  $selOp->_ref_sejour->loadRefsFwd();
  $selOp->_ref_sejour->loadRefsConsultAnesth();
  $selOp->_ref_sejour->_ref_consult_anesth->loadRefsFwd();

  $patient = $selOp->_ref_sejour->_ref_patient;
  $patient->loadRefsConsultations();

  // Chargement des praticiens
  $listAnesths = array();
  if (!$offline || $offline == 'false') {
    $perm_to_duplicate = CAppUI::gconf("dPcabinet CConsultation csa_duplicate_by_cabinet") ? false : true;
    $anesths           = new CMediusers();
    $listAnesths       = $anesths->loadAnesthesistes($perm_to_duplicate ? PERM_READ : PERM_EDIT);
  }

  foreach ($patient->_ref_consultations as $consultation) {
    $consultation->loadRefConsultAnesth();
    $consultation->loadRefPlageConsult();

    foreach ($consultation->_refs_dossiers_anesth as $_dossier_anesth) {
      $_dossier_anesth->loadRefOperation();
    }
  }

  $onSubmit = "return onSubmitFormAjax(this,
    function(){
      if (window.refreshFicheAnesth) {refreshFicheAnesth();}
      else if (window.printFicheBloc) {printFicheBloc();}
      else {window.opener.chooseAnesthCallback.defer(); window.close();}
    }
  )";

  $smarty = new CSmartyDP("modules/dPcabinet");
  $smarty->assign("selOp", $selOp);
  $smarty->assign("patient", $patient);
  $smarty->assign("listAnesths", $listAnesths);
  $smarty->assign("onSubmit", $onSubmit);
  $smarty->assign("create_dossier_anesth", $create_dossier_anesth);
  $smarty->assign("mod_ambu", $mod_ambu);
  $smarty->display("inc_choose_dossier_anesth");

  return;
}

$dossier_anesth->loadRefsDocs();
$dossier_anesth->loadRefsInfoChecklistItem(true);
$dossier_anesth->loadRefScoreLee();
$dossier_anesth->loadRefScoreMet();
$dossier_anesth->loadRefScoreHemostase();
$consult = $dossier_anesth->loadRefConsultation();
$consult->loadRefPlageConsult();

if ($pdf) {
  // Si le modèle est redéfini, on l'utilise
  $model = CCompteRendu::getSpecialModel($consult->_ref_chir, "CConsultAnesth", "[FICHE ANESTH]");

  if ($model->_id) {
      if ($multi) {
          echo CCompteRendu::getDocForObject($model, $dossier_anesth, $model->factory, $auto_print);
          return;
      } else {
          CCompteRendu::streamDocForObject($model, $dossier_anesth, $model->factory, $auto_print);
      }
  }
}

$consult->loadRefsFwd();
$consult->loadRefsDossiersAnesth();
$consult->loadRefsExamsComp();
$consult->loadRefsExamNyha();
$consult->loadRefsExamPossum();

$dossier_anesth->loadRefs();
$dossier_anesth->_ref_sejour->loadRefDossierMedical();
$dossier_anesth->_ref_operation->loadRefTypeAnesth();

$other_intervs   = array();
$pos_curr_interv = 0;

foreach ($consult->loadRefsDossiersAnesth() as $_dossier_anesth) {
  if ($_dossier_anesth->operation_id) {
    $_op = $_dossier_anesth->loadRefOperation();
    if (!$_op->annulee) {
      $_op->loadRefPlageOp();
      $_op->loadRefChir();
      $other_intervs[$_op->_id] = $_op;
    }
  }
}

ksort($other_intervs);

if (count($other_intervs) > 1) {
  $pos_curr_interv = array_search($dossier_anesth->operation_id, array_keys($other_intervs));
  $pos_curr_interv++;
}

// Lignes de prescription en prémédication
if (CModule::getActive("dPprescription")) {
  $prescription = $dossier_anesth->_ref_sejour->loadRefPrescriptionSejour();
  $prescription->loadRefsLinesElement();
  $prescription->loadRefsLinesMed();
  $prescription->loadRefsPrescriptionLineMixes();
  $prescription->loadRefsLinesPerop();

  $show_premed_chir_fiche = CAppUI::gconf("dPprescription CPrescription show_premed_chir_fiche");

  foreach ($prescription->_ref_prescription_lines_element as $_line_elt) {
    if (!$_line_elt->premedication) {
      continue;
    }
    if (!$show_premed_chir_fiche || !$_line_elt->_ref_praticien->isAnesth()) {
      continue;
    }
    if ($_line_elt->date_arret) {
      continue;
    }
    $_line_elt->loadRefsPrises();
    $lines[] = $_line_elt;
  }

  if ($prescription->_id) {
    $lines_tp["pre_admission"] = array();
    $entree_sejour             = $prescription->_ref_object->entree;
  }

  foreach ($prescription->_ref_prescription_lines as $_line_med) {

    if ($_line_med->traitement_personnel && !$_line_med->original_line_id) {
      CPrescription::setLineConduite($_line_med, $prescription, $entree_sejour, true);
      $lines_tp["pre_admission"][$_line_med->_contexte_tp][$_line_med->_id] = $_line_med;
    }
    if (!$_line_med->premedication) {
      continue;
    }
    if (!$show_premed_chir_fiche || !$_line_med->_ref_praticien->isAnesth()) {
      continue;
    }
    if ($_line_med->date_arret) {
      continue;
    }
    $_line_med->loadRefsPrises();
    $_line_med->loadRefMomentArret();
    $lines[] = $_line_med;
  }

  foreach ($prescription->_ref_prescription_line_mixes as $_line_mix) {
    if (!$_line_mix->premedication) {
      continue;
    }
    $_line_mix->loadRefPraticien();
    if (!$show_premed_chir_fiche || !$_line_mix->_ref_praticien->isAnesth()) {
      continue;
    }
    if ($_line_mix->date_arret) {
      continue;
    }
    $_line_mix->loadRefsLines();
    $lines[] = $_line_mix;
  }
  foreach ($prescription->_ref_lines_perop as $type => $tab_line) {
    foreach ($tab_line as $_line_per_op) {
      if ($type == "med" || $type == "elt") {
        /* @var CPrescriptionLine $_line_per_op */
        $_line_per_op->loadRefsPrises();
      }
      if ($type == "mix") {
        /* @var CPrescriptionLineMix $_line_per_op */
        $_line_per_op->loadRefPraticien();
        $_line_per_op->loadRefsLines();
      }

      $lines_per_op[] = $_line_per_op;
    }
  }
}

$praticien =& $consult->_ref_chir;
$patient   =& $consult->_ref_patient;
$patient->loadRefDossierMedical();
$patient->loadIPP();
$patient_insnir = $patient->loadRefPatientINSNIR();
$patient_insnir->createDatamatrix($patient_insnir->createDataForDatamatrix());

$dossier_medical =& $patient->_ref_dossier_medical;

$patient->loadRefsConsultations();
$dossiers = array();
foreach ($patient->_ref_consultations as $consultation) {
  $consultation->loadRefConsultAnesth();
  $consultation->loadRefPlageConsult();
  foreach ($consultation->_refs_dossiers_anesth as $_dossier_anesth) {
    $_dossier_anesth->_ref_consultation = $consultation;
    $_dossier_anesth->loadRefOperation();
    $dossiers[$_dossier_anesth->_id] = $_dossier_anesth;
  }
}

// Chargement des elements du dossier medical
$dossier_medical->loadRefsAntecedents();
$dossier_medical->countAllergies();
$dossier_medical->loadRefsTraitements();
$dossier_medical->loadRefsEtatsDents();

$dossier_medical->loadRefPrescription();
if ($dossier_medical->_ref_prescription && $dossier_medical->_ref_prescription->_id) {
  foreach ($dossier_medical->_ref_prescription->_ref_prescription_lines as $_line) {
    if ($_line->fin && $_line->fin <= CMbDT::date()) {
      unset($dossier_medical->_ref_prescription->_ref_prescription_lines[$_line->_id]);
    }
    // Si des lignes sont présentes dans la conduite à tenir, on retire les lignes avec le même code cis (sinon doublon de tp)
    if (array_key_exists("pre_admission", $lines_tp)) {
      foreach ($lines_tp["pre_admission"] as $_context_tp => $_lines_med) {
        foreach ($_lines_med as $_line_med) {
          if ($_line_med->code_cis == $_line->code_cis) {
            unset($dossier_medical->_ref_prescription->_ref_prescription_lines[$_line->_id]);
            continue;
          }
        }
      }
    }
    $_line->loadRefsPrises();
  }
}


$etats = array();
if (is_array($dossier_medical->_ref_etats_dents)) {
  foreach ($dossier_medical->_ref_etats_dents as $etat) {
    if ($etat->etat != null) {
      switch ($etat->dent) {
        case 10:
        case 50:
          $position = "Central haut";
          break;
        case 30:
        case 70:
          $position = "Central bas";
          break;
        default:
          $position = $etat->dent;
      }
      if (!isset ($etats[$etat->etat])) {
        $etats[$etat->etat] = array();
      }
      $etats[$etat->etat][] = $position;
    }
  }
}
$sEtatsDents = "";
foreach ($etats as $key => $list) {
  sort($list);
  $sEtatsDents .= "- " . ucfirst($key) . " : " . implode(", ", $list) . "\n";
}

// Affichage des données
$listChamps = array(
  1 => array("creatinine", "_clairance", "fibrinogene", "na", "k", "inr", "date_analyse", "hb", "ht"),
  2 => array("tp", "tca", "tsivy", "ecbu", "result_com", "histoire_maladie", "ht_final", "plaquettes")
);

$dossier_anesth->loadRefsRisques();
foreach ($listChamps as $keyCol => $aColonne) {
  foreach ($aColonne as $keyChamp => $champ) {
    $verifchamp = true;
    if ($champ == "tca") {
      $champ2 = $dossier_anesth->tca_temoin;
    }
    else {
      $champ2 = false;
      if (($champ == "ecbu" && $dossier_anesth->ecbu == "?") || ($champ == "tsivy" && $dossier_anesth->tsivy == "00:00:00")) {
        $verifchamp = false;
      }
    }

    $champ_exist = $champ2 ||
      ($verifchamp && isset($dossier_anesth->$champ) && $dossier_anesth->$champ) ||
      isset($dossier_anesth->_risques_anesth[$champ]);
    if (!$champ_exist) {
      unset($listChamps[$keyCol][$keyChamp]);
    }
  }
}

// Tableau d'unités
$unites                     = array();
$unites["hb"]               = array("nom" => "Hb", "unit" => "g/dl");
$unites["ht"]               = array("nom" => "Ht", "unit" => "%");
$unites["ht_final"]         = array("nom" => "Ht final", "unit" => "%");
$unites["plaquettes"]       = array("nom" => "Plaquettes", "unit" => "(x1000) /mm3");
$unites["creatinine"]       = array("nom" => "Créatinine", "unit" => "mg/l");
$unites["_clairance"]       = array("nom" => "Clairance de Créatinine", "unit" => "ml/min");
$unites["_clairance"]       = array("nom" => "Clairance de Créatinine", "unit" => "ml/min");
$unites["fibrinogene"]      = array("nom" => "Fibrinogène", "unit" => "g/l");
$unites["na"]               = array("nom" => "Na+", "unit" => "mmol/l");
$unites["k"]                = array("nom" => "K+", "unit" => "mmol/l");
$unites["tp"]               = array("nom" => "TP", "unit" => "%");
$unites["tca"]              = array("nom" => "TCA", "unit" => "s");
$unites["tsivy"]            = array("nom" => "TS Ivy", "unit" => "");
$unites["ecbu"]             = array("nom" => "ECBU", "unit" => "");
$unites["result_com"]       = array("nom" => "Commentaire", "unit" => "");
$unites["histoire_maladie"] = array("nom" => "Histoire de la maladie", "unit" => "");
$unites["date_analyse"]     = array("nom" => "Date", "unit" => "");
$unites["chlore"]           = array("nom" => "Chlore", "unit" => "mmol/l");
$unites["inr"]              = array("nom" => "INR", "unit" => "");


$acces_fiche = "../../dPcabinet/templates/" . CAppUI::gconf("dPcabinet CConsultAnesth feuille_anesthesie") . ".tpl";
$use_moebius = CModule::getActive("moebius") && CRisque::countRisques($dossier_anesth) ? 1 : 0;
if ($use_moebius) {
  $acces_fiche = "../../moebius/templates/print_fiche.tpl";
  $dossier_anesth->loadRefsRisques();
  $moebius = new CMoebiusXML();

  $atcds_details = $moebius->loadRefsAtcdsDetails($patient->_id);
  $fiche_moebius = CMoebiusAPI::tryExportConsult($dossier_anesth->_id, "graphique-risque");

  // Redraw the antecedents array like the summary view
  $atcds_by_type = $consult->_ref_patient->_ref_dossier_medical->_ref_antecedents_by_type;
  $redraw_atcds  = array();

  // Get atcd chir
  $moebius->readFile('atcd_chirurgicaux');
  $atcd_chir_type = $moebius->getAllAssociatedIdsToChirAtcds();

  // Sort atcds
  foreach ($atcds_by_type as $_atcds) {
    if ($_atcds) {
      foreach ($_atcds as $_atcd) {
        $redraw_appareil = CAppUI::tr('CAntecedent.appareil.' . ($_atcd->appareil ?? "aucun"));

        // Chir and med work slightly differently
        // For chir, use ids to get the category (appareil)
        // Remove the category name when it's a text field (e.g. "Chirurgie cardiaque: remarque" becomes "remarque")
        if ($_atcd->type === 'chir') {
          // Get the moebius id (sometimes uses key:value, sometimes no, sometimes ...)
          $id400 = $_atcd->loadLastId400('moebius_atcd')->id400;
          $name = $_atcd->rques;
          foreach ($atcd_chir_type as $_appareil_name => $_chir_type) {
            if (strpos($id400, ":") !== false) {
              $id400 = explode(':', $_atcd->loadLastId400('moebius_atcd')->id400);
              $id400 = (isset($id400[1]) && $id400[1] !== "") ? $id400[1] : $id400[0];
            }

            // If any of the last moebius id is in the chir types, affect the name of the type
            if (in_array($id400, $_chir_type)) {
              $redraw_appareil = $_appareil_name;
            }
          }

          if (strpos($_atcd->rques, ": ") !== false) {
            $_atcd->rques = trim(explode(':', $_atcd->rques)[1]);
          }
        }

        $_atcd->rques = (strpos($_atcd->rques, "RAS - ") === 0) ? CAppUI::tr("RAS") : $_atcd->rques;

        $redraw_atcds[$_atcd->type][$redraw_appareil][$_atcd->_id] = $_atcd;

        $_atcd->rques = str_replace("alle:", "", $_atcd->rques);

        $position_remarque = strpos($_atcd->rques, ": ");

        if ($position_remarque) {
          $categorie = substr($_atcd->rques, 0, $position_remarque);
          $remarque  = substr($_atcd->rques, $position_remarque + 2);

          // If it's a rques of the category (e.g. Uro) or if it's a rques of an item (e.g. insuffisance renale)
          if ($categorie === $_atcd->appareil) {
              $_atcd->rques = $remarque;
          } else {
              $_atcd->rques                 = $categorie;
              $atcds_details[$_atcd->_id][] = $remarque;
          }
        }
        $_atcd->updateFormFields();
      }
    }
  }

  $sort_atcds     = $redraw_atcds;
  $already_sorted = ["alle", "fam", "chir", "anesth", "addiction", "trans", "med", "obst", "gyn"];
  foreach ($redraw_atcds as $type => $_value) {
    if (in_array($type, $already_sorted)) {
      $sort_atcds[CAppUI::tr('CAntecedent.type.' . $type)] = $_value;
      unset($sort_atcds[$type]);
    }
  }

  $redraw_atcds = $sort_atcds;

  $img_64_encoded = null;
  $img_type       = null;
  if ($offline && $fiche_moebius->_id) {
      try {
          $img_str        = CThumbnail::makeThumbnail(
              $fiche_moebius->_id,
              CClassMap::getInstance()->getShortName($fiche_moebius),
              "medium",
              null,
              0,
              false,
              "high",
              null,
              false
          );
          $img_64_encoded = base64_encode($img_str);
          $img_type       = $fiche_moebius->file_type;
      }
      catch (CMbException $e) {
        //Ne rien faire, l'image ne sera pas affiché
      }
  }
}

$smarty = new CSmartyDP();

$smarty->assign("dossiers", $dossiers);
$smarty->assign("display", $display);
$smarty->assign("offline", $offline);
$smarty->assign("unites", $unites);
$smarty->assign("listChamps", $listChamps);
$smarty->assign("dossier_anesth", $dossier_anesth);
$smarty->assign("etatDents", $sEtatsDents);
$smarty->assign("print", $print);
$smarty->assign("praticien", new CUser());
$smarty->assign("lines", $lines);
$smarty->assign("lines_per_op", $lines_per_op);
$smarty->assign("lines_tp", $lines_tp);
$smarty->assign("multi", $multi);
$smarty->assign("dossier_medical_sejour", $dossier_anesth->_ref_sejour->_ref_dossier_medical);
$smarty->assign("other_intervs", $other_intervs);
$smarty->assign("pos_curr_interv", $pos_curr_interv);
if ($use_moebius) {
  $smarty->assign("img_64_encoded", $img_64_encoded);
  $smarty->assign("img_type", $img_type);
  $smarty->assign("atcds_details", $atcds_details);
  $smarty->assign("fiche_moebius", $fiche_moebius);
  $smarty->assign("redraw_atcds", $redraw_atcds);
  $smarty->assign("risques_exam_clinique", ["risque_cardio", "risque_respiratoire"]);
}

$smarty->display($acces_fiche);
