<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CConsultationCategorie;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CItemLiaison;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CProtocole;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanningOp\CTypeAnesth;
use Ox\Mediboard\Prescription\CPrescription;

if (CAppUI::pref('create_dhe_with_read_rights')) {
  CCanDo::checkRead();
}
else {
  CCanDo::checkEdit();
}

$chir_id_session = false;
if (CAppUI::conf('dPplanningOp CSejour use_session_praticien')) {
  $chir_id_session = true;
}

$sejour_id        = CView::get('sejour_id', 'str', true);
$operation_id     = CView::get('operation_id', 'str');
$consultation_id  = CView::get('consultation_id', 'str');
$patient_id       = CView::get('patient_id', 'ref class|CPatient');
$chir_id          = CView::get('chir_id', 'ref class|CMediusers', true);
$grossesse_id     = CView::get('grossesse_id', 'ref class|CGrosesse');
$protocole_id     = CView::get('protocole_id', 'ref class|CProtocole');
$modal            = CView::get('modal', 'bool default|0');
$hour_urgence     = CView::get("hour_urgence", "num");
$min_urgence      = CView::get("min_urgence", "num");
$date_urgence     = CView::get("date_urgence", "date");
$salle_id         = CView::get("salle_id", "ref class|CSalle");
$action           = CView::get('action', 'enum list|new_sejour|new_operation|new_consultation|edit_sejour|edit_operation|edit_consultation default|new_sejour');

CView::checkin();

$groups = CGroups::loadGroups();
$group = CGroups::loadCurrent();

$user = CMediusers::get();

$sejour = new CSejour();
$operation = new COperation();
$consult = new CConsultation();
if ($operation_id) {
  $operation->load($operation_id);

  CAccessMedicalData::logAccess($operation);

  $operation->loadRefChir();

  $sejour = $operation->loadRefSejour();
  $action = 'edit_operation';
  $operation->loadRefsFwd();

  $chir = $sejour->loadRefPraticien();
  $patient = $sejour->loadRefPatient();
}
elseif ($consultation_id) {
  $consult->load($consultation_id);

  CAccessMedicalData::logAccess($consult);

  $consult->loadRefPraticien();
  $consult->loadRefsFwd();
  $sejour = $consult->loadRefSejour();
  $action = 'edit_consultation';

  $chir = $sejour->loadRefPraticien();
  $patient = $sejour->loadRefPatient();
}
elseif ($sejour_id) {
  $sejour->load($sejour_id);

  CAccessMedicalData::logAccess($sejour);

  $action = 'edit_sejour';

  $chir = $sejour->loadRefPraticien();
  $patient = $sejour->loadRefPatient();
}
else {
  $chir = new CMediusers();
  if ($user->isPraticien() && !$chir_id) {
    $chir = $user;
    $chir_id = $user->_id;
  }
  elseif ($chir_id) {
    $chir->load($chir_id);
  }

  $patient = new CPatient();
  if ($patient_id) {
    $patient->load($patient_id);
  }

  $sejour->_ref_patient = $patient;
  $sejour->patient_id = $patient_id;
  $sejour->_ref_praticien = $chir;
  $sejour->praticien_id = $chir->_id;

  $operation->valueDefaults();
  $operation->_ref_praticien = $chir;
  $operation->loadRefPlageOp();

  $consult->_ref_praticien = $chir;
  $consult->_praticien_id = $chir->_id;
  $consult->_ref_patient = $patient;
  $consult->patient_id = $patient->_id;
}

if ($sejour->_id) {
  $chir = $sejour->loadRefPraticien();
  $patient = $sejour->loadRefPatient();
}
else {
  $sejour->_duree_prevue = 0;
  $sejour->_duree_prevue_heure = 8;
  $sejour->type = 'ambu';
}

$chir->loadRefFunction();

$sejour->loadRefsOperations();
$sejour->loadRefsConsultations();
$sejour->loadDiagnosticsAssocies(false, true);
$sejour->loadExtDiagnostics();
$sejour->loadRefUfs();
$sejour->loadRefChargePriceIndicator();
$sejour->loadRefDisciplineTarifaire();
$sejour->loadRefEtablissementProvenance();
$sejour->loadRefEtablissementTransfert();
$sejour->loadRefServiceMutation();
$sejour->loadRefServiceProvenance();
$sejour->loadRefService();
$sejour->loadRefsAffectations();
$sejour->loadRefGrossesse();
$sejour->loadRefAdresseParPraticien();
$sejour->loadRefsDocItems();
$sejour->updateFieldsFacture();
CAffectation::massUpdateView($sejour->_ref_affectations);
foreach ($sejour->_ref_consultations as $_consultation) {
  $_consultation->loadRefsFwd();
}

$operation->loadRefPlageOp();
if (!$operation->_id) {
  $operation->_ref_praticien = $sejour->_ref_praticien;
  $operation->_time_op = "00:00:00";
  $operation->_datetime = "";
  $operation->salle_id = $salle_id;

  if ($date_urgence) {
    $operation->date = $date_urgence;
  }
  if ($hour_urgence && isset($min_urgence)) {
    $hour = intval(substr($hour_urgence, 0, 2));
    $min = intval(substr($min_urgence, 0, 2));
    $operation->_time_urgence = "$hour:$min:00";
  }
  else {
    $time_config = str_pad(CAppUI::conf("dPplanningOp COperation hour_urgence_deb"), 2, "0", STR_PAD_LEFT).":00:00";

    $time = CMbDT::transform(CMbDT::time(), null , "%H:00:00");

    if ($time < $time_config) {
      $time = $time_config;
    }

    $operation->_time_urgence = $time;
  }
}

$protocole = new CProtocole();
if ($protocole_id) {
  $protocole->load($protocole_id);
  $protocole->loadRefChir();
}

if (!$sejour->_id && $protocole->_id) {
  $sejour->libelle = $protocole->libelle_sejour;
}

if (CModule::getActive("dPprescription") && $sejour->_id) {
  $sejour->loadRefsPrescriptions();

  if (count($sejour->_ref_prescriptions)) {
    CPrescription::massCountMedsElements($sejour->_ref_prescriptions);

    foreach ($sejour->_ref_prescriptions as $_prescription) {
      if (is_array($_prescription->_counts_by_chapitre)) {
        CMbArray::removeValue("0", $_prescription->_counts_by_chapitre);
      }
    }
  }
}

$patient->loadRefsCorrespondants();
$patient->loadRefsPatientHandicaps();

if (CAppUI::gconf('dPhospi prestations systeme_prestations') == 'standard') {
  $sejour->loadRefPrestation();
}
else {
  /** @var CItemLiaison[] $links_prestation */
  $links_prestation = $sejour->loadBackRefs("items_liaisons");
  CStoredObject::massLoadFwdRef($links_prestation, "item_souhait_id");
  CStoredObject::massLoadFwdRef($links_prestation, "item_realise_id");
  CStoredObject::massLoadFwdRef($links_prestation, "sous_item_id");

  foreach ($links_prestation as $_link) {
    $_link->loadRefItem();
    $_link->loadRefItemRealise();
    $_link->loadRefSousItem();
  }
}

$modes_entree = array();
if (CAppUI::conf('dPplanningOp CSejour use_custom_mode_entree')) {
  $sejour->loadRefModeEntree();

  $mode = new CModeEntreeSejour();
  $modes_entree = $mode->loadGroupList(array('actif' => '= "1"'), 'libelle');
}

$modes_sortie = array();
if (CAppUI::conf('dPplanningOp CSejour use_custom_mode_sortie')) {
  $sejour->loadRefModeSortie();

  $mode = new CModeSortieSejour();
  $modes_sortie = $mode->loadGroupList(array('actif' => '= "1"'), 'libelle');
}

$service = new CService();
$services = $service->loadGroupList();

$list_categories = array();
$categories = array();
if ($chir->_id) {
  $categorie = new CConsultationCategorie();
  /** @var CConsultationCategorie[] $categories */
  $categories = $categorie->loadList(array('function_id' => " = '$chir->function_id'"), 'nom_categorie ASC');

  foreach ($categories as $_category) {
    $list_categories[$_category->_id] = array(
      'nom_icone'   => $_category->nom_icone,
      'duree'       => $_category->duree,
      'commentaire' => $_category->commentaire
    );
  }
}

$types_anesth = new CTypeAnesth();
$types_anesth = $types_anesth->loadGroupList();

$anesthesistes = $user->loadAnesthesistes(PERM_READ);

$blocs = CGroups::loadCurrent()->loadBlocs(PERM_READ);

$smarty = new CSmartyDP();
$smarty->assign('sejour'                  , $sejour);
$smarty->assign('chir'                    , $chir);
$smarty->assign('patient'                 , $patient);
$smarty->assign('groups'                  , $groups);
$smarty->assign('group'                   , CGroups::loadCurrent());
$smarty->assign('services'                , $services);
$smarty->assign('protocole'               , $protocole);
$smarty->assign('user'                    , $user);
$smarty->assign('modal'                   , $modal);
$smarty->assign('ufs'                     , CUniteFonctionnelle::getUFs($sejour));
$smarty->assign('cpis'                    , CChargePriceIndicator::getList());
$smarty->assign('modes_entree'            , $modes_entree);
$smarty->assign('modes_sortie'            , $modes_sortie);
$smarty->assign('prestations'             , CPrestation::loadCurrentList());
$smarty->assign('consult'                 , $consult);
$smarty->assign('categories_consult'      , $categories);
$smarty->assign('list_categories_consult' , $list_categories);
$smarty->assign('operation'               , $operation);
$smarty->assign("types_anesth"            , $types_anesth);
$smarty->assign("anesthesistes"           , $anesthesistes);
$smarty->assign("blocs"                   , $blocs);
$smarty->assign("date_min"                , CMbDT::date());
$smarty->assign("date_max"                , CMbDT::date("+".CAppUI::conf("dPplanningOp COperation nb_jours_urgence")." days", CMbDT::date()));
$smarty->assign('action'                  , $action);
$smarty->display('vw_dhe');
