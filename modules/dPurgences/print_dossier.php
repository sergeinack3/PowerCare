<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Medicament\CMedicamentProduit;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Urgences\CRPU;

CCanDo::checkRead();
$rpu_id  = CView::get("rpu_id", "ref class|CRPU");
$offline = CView::get("offline", "bool default|0");
CView::checkin();

$today = date("d/m/Y");

// Création du rpu
$rpu = new CRPU();
$rpu->load($rpu_id);
if ($offline) {
  $rpu->loadRefSejour();
}
else {
  $rpu->loadComplete();
}
$rpu->loadRefSejourMutation();
$rpu->loadRefReevaluationsPec();

$sejour = $rpu->_ref_sejour;
$sejour->loadRefsConsultations();
$sejour->loadListConstantesMedicales();
$sejour->loadSuiviMedical();
$patient = $sejour->_ref_patient;
$patient->loadRefLatestConstantes();
$patient->loadIPP();
$patient->loadRefDossierMedical();
$patient->loadRefsCorrespondantsPatient();
$patient->loadRefsCorrespondants();
$patient->loadRefPhotoIdentite();
$patient->loadRefPatientINSNIR();
$dossier_medical = $patient->_ref_dossier_medical;
$dossier_medical->countAntecedents();
$dossier_medical->loadRefPrescription();
$dossier_medical->loadRefsTraitements();

$consult = $sejour->_ref_consult_atu;
$consult->loadRefPatient();
$consult->loadRefPraticien();
$consult->loadRefsBack();
$consult->loadRefsDocs();

foreach ($consult->_ref_actes_ccam as $_ccam) {
  $_ccam->loadRefExecutant();
}

$constantes_medicales_grid = CConstantesMedicales::buildGrid(
  $sejour->_list_constantes_medicales, false, true, CConstantesMedicales::guessHost($sejour)
);

$formulaires = null;
if (CModule::getActive("forms")) {
  $params = array(
    "detail"          => 3,
    "reference_id"    => $sejour->_id,
    "reference_class" => $sejour->_class,
    "target_element"  => "ex-objects-$sejour->_id",
    "print"           => 1,
  );

  $formulaires = CApp::fetch("forms", "ajax_list_ex_object", $params);
}

$dossier     = array();
$list_lines  = array();
$atc_classes = array();

if (CModule::getActive("dPprescription")) {
  // Chargement du dossier de soins cloturé
  $prescription = $sejour->loadRefPrescriptionSejour();

  // Chargement des lignes
  $prescription->loadRefsLinesMedComments("0", "0", "1", "", "", "0", "1");
  $prescription->loadRefsLinesElementsComments();
  $prescription->loadRefsPrescriptionLineMixes();

  if (count($prescription->_ref_prescription_line_mixes)) {
    foreach ($prescription->_ref_prescription_line_mixes as $_prescription_line_mix) {
      $_prescription_line_mix->loadRefsLines();
      $_prescription_line_mix->calculQuantiteTotal();
      $_prescription_line_mix->loadRefPraticien();
      foreach ($_prescription_line_mix->_ref_lines as $_perf_line) {
        $list_lines[$_prescription_line_mix->type_line][$_perf_line->_id] = $_perf_line;
        $_perf_line->loadRefsAdministrations();
        foreach ($_perf_line->_ref_administrations as $_administration_perf) {
          $_administration_perf->loadRefAdministrateur();
          if (!$_administration_perf->planification) {
            $dossier[CMbDT::date($_administration_perf->dateTime)][$_prescription_line_mix->type_line][$_perf_line->_id][$_administration_perf->quantite][$_administration_perf->_id] = $_administration_perf;
          }
        }
      }
    }
  }

  // Parcours des lignes de medicament et stockage du dossier cloturé
  if (count($prescription->_ref_lines_med_comments["med"])) {
    foreach ($prescription->_ref_lines_med_comments["med"] as $_atc => $lines_by_type) {
      if (!isset($atc_classes[$_atc])) {
        $medicament_produit = new CMedicamentProduit();
        $atc_classes[$_atc] = $medicament_produit->getLibelleATC($_atc);
      }

      foreach ($lines_by_type as $med_id => $_line_med) {
        $list_lines["med"][$_line_med->_id] = $_line_med;

        $_line_med->loadRefsAdministrations();
        foreach ($_line_med->_ref_administrations as $_administration_med) {
          $_administration_med->loadRefAdministrateur();
          if (!$_administration_med->planification) {
            $dossier[CMbDT::date($_administration_med->dateTime)]["med"][$_line_med->_id][$_administration_med->quantite][$_administration_med->_id] = $_administration_med;
          }
        }
      }
    }
  }

  // Parcours des lignes d'elements
  if (count($prescription->_ref_lines_elements_comments)) {
    foreach ($prescription->_ref_lines_elements_comments as $chap => $_lines_by_chap) {
      foreach ($_lines_by_chap as $_lines_by_cat) {
        foreach ($_lines_by_cat["comment"] as $_line_elt_comment) {
          $_line_elt_comment->loadRefPraticien();
        }

        foreach ($_lines_by_cat["element"] as $_line_elt) {
          $list_lines[$chap][$_line_elt->_id] = $_line_elt;
          $_line_elt->loadRefsAdministrations();
          foreach ($_line_elt->_ref_administrations as $_administration_elt) {
            $_administration_elt->loadRefAdministrateur();
            $_administration_elt->loadRefsExamenNouveauNe();
            if (!$_administration_elt->planification) {
              $dossier[CMbDT::date($_administration_elt->dateTime)][$chap][$_line_elt->_id][$_administration_elt->quantite][$_administration_elt->_id] = $_administration_elt;
            }
          }
        }
      }
    }
  }
}

ksort($dossier);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("rpu"                      , $rpu);
$smarty->assign("patient"                  , $patient);
$smarty->assign("sejour"                   , $sejour);
$smarty->assign("consult"                  , $consult);
$smarty->assign("today"                    , $today);
$smarty->assign("offline"                  , $offline);
$smarty->assign("formulaires"              , $formulaires);
$smarty->assign("dossier"                  , $dossier);
$smarty->assign("list_lines"               , $list_lines);
$smarty->assign("formulaires"              , $formulaires);
$smarty->assign("praticien"                , new CMediusers());
$smarty->assign("atc_classes"              , $atc_classes);
$smarty->assign("dossier_medical"          , $dossier_medical);
$smarty->assign("constantes_medicales_grid", $constantes_medicales_grid);
if (CModule::getActive("dPprescription")) {
  $smarty->assign("prescription"           , $prescription);
}
$smarty->display("print_dossier");
