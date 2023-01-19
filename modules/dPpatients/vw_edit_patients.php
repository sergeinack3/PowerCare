<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\CViewHistory;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CIdInterpreter;
use Ox\Mediboard\Fse\CFseFactory;
use Ox\Mediboard\Jfse\Domain\Vital\CPatientBuilder;
use Ox\Mediboard\Jfse\Domain\Vital\Patient;
use Ox\Mediboard\Jfse\Domain\Vital\VitalCardService;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientFamilyLink;
use Ox\Mediboard\Patients\CPatientHandicap;
use Ox\Mediboard\Patients\CPatientINSNIR;
use Ox\Mediboard\Provenance\CProvenance;

CCanDo::checkEdit();

$patient_id          = CView::get("patient_id", "ref class|CPatient", true);
$name                = CView::get("name", "str");
$firstName           = CView::get("firstName", "str");
$naissance_day       = CView::get("naissance_day", "numchar");
$naissance_month     = CView::get("naissance_month", "numchar");
$naissance_year      = CView::get("naissance_year", "num");
$useVitale           = CView::get("useVitale", "bool");
$callback            = CView::get("callback", "str");
$modal               = CView::get("modal", "bool default|0");
$praticien_id        = CView::get("praticien_id", "ref class|CMediusers");
$validate_identity   = CView::get('validate_identity', 'bool');
$sexe                = CView::get('sexe', 'str');
$nom_jeune_fille     = CView::get('nom_jeune_fille', 'str');
$administrative_data = CValue::getOrSessionAbs('administrative_data', true);

CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);
$patient->canDo()->needsEdit();
$patient->loadRefPhotoIdentite();
$patient->countDocItems();
$patient->loadRefsCorrespondantsPatient();
$patient->loadRefsCorrespondants();
$patient->countINS();
$patient->loadRefBMRBHRe();
$patient->loadRefsDirectivesAnticipees();
$patient->isConsentTerreSante();
$patient->isConsentDMP();
$patient->isConsentMSSantePatient();
$patient->isConsentMSSantePro();
$patient->loadRefPatientState();
$patient->loadRefSourceIdentite();
$patient->loadRefsSourcesIdentite();
$patient->loadRefsPatientHandicaps();
$patient->loadCodeInseeNaissance();

CAccessMedicalData::logAccess($patient, 'modification');

// Chargement de l'ipp
$patient->loadIPP();
if (CModule::getActive("fse")) {
  $cv = CFseFactory::createCV();
  if ($cv) {
    $cv->loadIdVitale($patient);
  }
}

$group = CGroups::loadCurrent();

if (!$modal) {
  // Save history
  $params = array(
    "patient_id"      => $patient_id,
    "name"            => $name,
    "firstName"       => $firstName,
    "naissance_day"   => $naissance_day,
    "naissance_month" => $naissance_month,
    "naissance_year"  => $naissance_year,
  );
  CViewHistory::save($patient, $patient_id ? CViewHistory::TYPE_EDIT : CViewHistory::TYPE_NEW, $params);
}

if (!$patient_id) {
    if ($nom_jeune_fille) {
        $patient->nom_jeune_fille = $nom_jeune_fille;
        $patient->nom = $name;
    } else {
        $patient->nom_jeune_fille = $name;
    }
    $patient->prenom        = $firstName;
    $patient->prenoms       = $firstName;
    $patient->assure_nom    = $name;
    $patient->assure_prenom = $firstName;
    $patient->sexe          = $sexe;
    $patient->unescapeValues();

  if ($naissance_day && $naissance_month && $naissance_year) {
    $patient->naissance = sprintf('%04d-%02d-%02d', $naissance_year, $naissance_month, $naissance_day);
  }

  if (CAppUI::conf("dPpatients CPatient default_value_allow_sms", $group)) {
    $patient->allow_sms_notification = 1;
  }

  if ($code_regime = CAppUI::conf("dPpatients CPatient default_code_regime", $group)) {
    $patient->code_regime = $code_regime;
  }

  $patient->tutelle = CAppUI::gconf("dPpatients CPatient tutelle_mandatory") ? "" : "aucune";
}

// Peut etre pas besoin de verifier si on n'utilise pas VitaleVision
if ($useVitale && CAppUI::pref('LogicielLectureVitale') == 'none' && CModule::getActive("fse")) {
    if (CAppUI::pref("LogicielFSE") === 'jfse') {
        $patient = (new VitalCardService())->getPatientFromVitalCard($patient, CMediusers::get());
    } else {
        $patVitale = new CPatient();
        $cv        = CFseFactory::createCV();
        if ($cv) {
            $cv->getPropertiesFromVitale($patVitale, $administrative_data);
            $patVitale->nullifyEmptyFields();
            $patient->extendsWith($patVitale);
            $patient->updateFormFields();
            $patient->_bind_vitale = "1";
        }
    }
}

// Chargement du nom_fr du pays de naissance
if ($patient->_id) {
  $patient->updateNomPaysInsee();
}

//Family link
$patient_family_link             = new CPatientFamilyLink();
if ($patient_id) {
    $patient_family_link->patient_id = $patient_id;
    $patient_family_link->loadMatchingObject();
}
$patient_family_link->loadRefParent1();
$patient_family_link->loadRefParent2();

// Gestion des utilisateurs ayant des fonctions secondaires et prenant un rdv pour un praticien d'un autre cabinet
$curr_user = CMediusers::get();
$functions = array();
if (CAppUI::isCabinet()) {
  $functions[$curr_user->function_id] = $curr_user->_ref_function;
  foreach ($curr_user->loadRefsSecondaryFunctions() as $_sec_function) {
    $functions[$_sec_function->_id] = $_sec_function;
  }

  if (!$patient->_id) {
    $praticien = new CMediusers();
    $praticien->load($praticien_id);
    $patient->function_id = isset($functions[$praticien->function_id]) ? $praticien->function_id : CFunctions::getCurrent()->_id;
    $patient->group_id    = $praticien->_id ? $praticien->loadRefFunction()->group_id : $curr_user->loadRefFunction()->group_id;
  }
}
elseif (CAppUI::isGroup() && !$patient->_id) {
  $praticien = new CMediusers();
  $praticien->load($praticien_id);

  $patient->group_id = $praticien->_id ? $praticien->loadRefFunction()->group_id : $curr_user->loadRefFunction()->group_id;
}

// Iconographie du patient sur les systèmes tiers
$patient->loadExternalIdentifiers($group->_id);

// On charge les provenances si le module est installé
if ($module = CModule::getActive('provenance') && CAppUI::isGroup()) {
  // Chargement des provenances de l'établissement
  $prov        = new CProvenance();
  $ds          = $prov->getDS();
  $where       = [
    'group_id' => $ds->prepare("= ?", $patient->group_id),
    'actif'    => $ds->prepare("= ?", 1)
  ];
  $provenances = $prov->loadList($where);
  // Provenances du patient
  $patient->loadRefProvenancePatient();
  // Afficher le commentaire de la provenance dans le formulaire
  $patient->_commentaire_prov = $patient->_ref_provenance_patient->commentaire;
}

if (in_array($patient->status, ['PROV', 'VIDE'])) {
    $patient->_force_manual_source = 1;
}

$ins = $patient->loadRefPatientINSNIR();

if (!in_array($patient->status, ['RECUP', 'QUAL'])) {
    $ins = new CPatientINSNIR();
}

// En modification, si le sexe n'est pas renseigné : on présélectionne indéterminé
if ($patient->_id && !$patient->sexe) {
    $patient->sexe = 'i';
}

$smarty = new CSmartyDP();
$smarty->assign("patient", $patient);
$smarty->assign("useVitale", $useVitale);
$smarty->assign("callback", $callback);
$smarty->assign("modal", $modal);
$smarty->assign("patient_family_link", $patient_family_link);
$smarty->assign("functions", $functions);
$smarty->assign("function_id", (CAppUI::isCabinet()) ? CMediusers::get()->function_id : null);
$smarty->assign('validate_identity', $validate_identity);
$smarty->assign('ins', $ins);
$smarty->assign("patient_handicap_list", (new CPatientHandicap())->_specs["handicap"]->_list);
if ($module = CModule::getActive('provenance') && CAppUI::isGroup()) {
  $smarty->assign('provenances', $provenances);
}
$smarty->display("vw_edit_patients");
