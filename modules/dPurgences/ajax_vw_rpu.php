<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Urgences\CProtocoleRPU;
use Ox\Mediboard\Urgences\CRPU;
use Ox\Mediboard\Urgences\CRPUCategorie;
use Ox\Mediboard\Urgences\CRPULinkCat;
use Ox\Mediboard\Urgences\CRPUReservationBox;

CCando::checkRead();
$rpu_id                = CView::get("rpu_id", "ref class|CRPU");
$sejour_id             = CView::get("sejour_id", "ref class|CSejour");
$_responsable_id       = CView::get("_responsable_id", "ref class|CMediusers");
$view_mode             = CView::get("view_mode", "enum list|infirmier|medical default|infirmier");
$tab_mode              = CView::get("tab_mode", "bool default|0");
$show_rpu_consultation = CView::get("show_rpu_consultation", "bool default|0");

$group            = CGroups::get();
$user             = CMediusers::get();
$listResponsables = CAppUI::conf("dPurgences only_prat_responsable") ?
  $user->loadPraticiens(PERM_READ, $group->service_urgences_id, null, true) :
  $user->loadListFromType(null, PERM_READ, $group->service_urgences_id, null, true, true);

$listPrats = $user->loadPraticiens(PERM_READ, $group->service_urgences_id, null, true);

$rpu = new CRPU();
$rpu->load($rpu_id);

// Création d'un RPU pour un séjour existant
if ($sejour_id && !$rpu->_id) {
  $rpu            = new CRPU();
  $rpu->sejour_id = $sejour_id;
  $rpu->loadMatchingObject();
  $rpu->updateFormFields();
}

$rpu->loadRefBox()->loadRefChambre();
$rpu->loadRefIDEResponsable();
$rpu->loadRefReevaluationsPec();

if ($rpu->_id || $rpu->sejour_id) {
  // Mise en session de l'id de la consultation, si elle existe.
  $rpu->loadRefConsult();
  if ($rpu->_ref_consult->_id) {
    CView::setSession("selConsult", $rpu->_ref_consult->_id);
  }
  $rpu->loadFwdRef("_mode_entree_id");
  $rpu->loadRefsAttentes();
  $sejour = $rpu->_ref_sejour;
  $sejour->loadRefCurrAffectation();

  $patient = $sejour->_ref_patient;
    $patient->_homonyme = count($patient->getPhoning($sejour->entree));

    // Chargement du numero de dossier ($_NDA)
  $sejour->loadNDA();
  $sejour->loadRefPraticien(1);
  $sejour->loadRefsNotes();
  $praticien                         = $sejour->_ref_praticien;
  $listResponsables[$praticien->_id] = $praticien;

  $sejour->_ref_grossesse = $patient->loadLastGrossesse();
  $patient->loadLastAllaitement();
  if (CAppUI::conf("dPurgences CRPU use_session_responsable", $group)) {
    CView::setSession("_responsable_id", $rpu->_responsable_id);
  }
  $rpu->loadPossibleUpdateCcmu();
  $rpu->loadRefIOA();
}
else {
  $rpu->_responsable_id = $user->_id;
  $rpu->_entree         = CMbDT::dateTime();
  $sejour               = new CSejour();
  $patient              = new CPatient();
  $praticien            = new CMediusers();
  if (!in_array($user->_id, $listResponsables) && $_responsable_id && CAppUI::conf("dPurgences CRPU use_session_responsable", $group)) {
    $rpu->_responsable_id = $_responsable_id;
  }
}

CView::checkin();

CAccessMedicalData::logAccess($sejour);

// Chargement des boxes
[$services, $services_type] = CRPU::loadServices($sejour);

$module_orumip = CModule::getActive("orumip");
$orumip_active = $module_orumip && $module_orumip->mod_active;


$is_praticien = CAppUI::$user->isPraticien();

// Protocoles pour nouveau RPU :
// - Si un seul actif -> on applique les champs
// - Sinon, affichage du bouton de choix de protocole
$protocoles = array();
if (!$rpu->_id) {
  $protocoles = CProtocoleRPU::loadProtocoles();

  if (count($protocoles) === 1) {
    $protocole = reset($protocoles);

    $rpu->protocole_id = $protocole->_id;

    foreach ($protocole->getPlainFields() as $_field => $_value) {
      if (property_exists("CRPU", $_field)) {
        $rpu->$_field = $_value;
      }
      elseif (property_exists("CRPU", "_$_field")) {
        $rpu->{"_$_field"} = $_value;
      }
    }

    if ($rpu->box_id) {
      $rpu->loadRefBox();
      $sejour->service_id = $rpu->_ref_box->loadRefChambre()->service_id;
    }

    $protocole->loadRefsDocItemsGuids();
    $rpu->_docitems_guid = $protocole->_docitems_guid;

    $rpu->loadFwdRef("_mode_entree_id");
  }
  else {
    CStoredObject::massLoadFwdRef($protocoles, "mode_entree_id");
    /** @var CProtocoleRPU $_protocole */
    foreach ($protocoles as $_protocole) {
      $_protocole->_mode_entree_id_view = $_protocole->loadRefModeEntree()->_view;
      $_protocole->loadRefsDocItemsGuids();
    }
  }
}
//Load RPU category

$rpu->loadRefCategories();
$categorie_rpu  = new CRPUCategorie();
$categories_rpu = $categorie_rpu->loadGroupList(array("actif" => "= '1'"));

foreach ($categories_rpu as $_categorie_rpu) {
    $_categorie_rpu->loadRefIcone();
}
$link_cat         = new CRPULinkCat();
$link_cat->rpu_id = $rpu->_id;

$blocages_lit = CRPU::getBlocagesLits();

// Tableau de contraintes pour les champs du RPU
// Contraintes sur le mode d'entree / provenance
//$contrainteProvenance[6] = array("", 1, 2, 3, 4);
$contrainteProvenance[7] = array("", 1, 2, 3, 4, 6);
$contrainteProvenance[8] = array("", 5, 8);

// Contraintes sur le mode de sortie / destination
$contrainteDestination["mutation"]  = array("", 1, 2, 3, 4);
$contrainteDestination["transfert"] = array("", 1, 2, 3, 4);
$contrainteDestination["normal"]    = array("", 6, 7);

// Contraintes sur le mode de sortie / orientation
$contrainteOrientation["mutation"]  = array("", "NA", "HDT", "HO", "SC", "SI", "REA", "UHCD", "MED", "CHIR", "OBST");
$contrainteOrientation["transfert"] = array("", "NA", "HDT", "HO", "SC", "SI", "REA", "UHCD", "MED", "CHIR", "OBST");
$contrainteOrientation["normal"]    = array("", "NA", "FUGUE", "SCAM", "PSA", "REO");

$user = CMediusers::get();

$listResponsables = CAppUI::conf("dPurgences only_prat_responsable") ?
  $user->loadPraticiens(PERM_READ, $group->service_urgences_id, null, true) :
  $user->loadListFromType(null, PERM_READ, $group->service_urgences_id, null, true, true);

$smarty = new CSmartyDP();
$smarty->assign("rpu"                  , $rpu);
$smarty->assign("categories_rpu"       , $categories_rpu);
$smarty->assign("link_cat"             , $link_cat);
$smarty->assign("sejour"               , $sejour);
$smarty->assign("patient"              , $patient);
$smarty->assign("listPrats"            , $listPrats);
$smarty->assign("services"             , $services);
$smarty->assign("services_type"        , $services_type);
$smarty->assign("view_mode"            , $view_mode);
$smarty->assign("now"                  , CMbDT::dateTime());
$smarty->assign("blocages_lit"         , $blocages_lit);
$smarty->assign("consult_anesth"       , null);
$smarty->assign("tab_mode"             , $tab_mode);
$smarty->assign("ufs"                  , CUniteFonctionnelle::getUFs($sejour));
$smarty->assign("reservations_box"     , CRPUReservationBox::loadCurrentReservations());
$smarty->assign("listResponsables"     , $listResponsables);
$smarty->assign("protocoles"           , $protocoles);
$smarty->assign("list_mode_entree"     , CModeEntreeSejour::listModeEntree());
$smarty->assign("list_mode_sortie"     , CModeSortieSejour::listModeSortie());
$smarty->assign("contrainteProvenance" , $contrainteProvenance);
$smarty->assign("contrainteDestination", $contrainteDestination);
$smarty->assign("contrainteOrientation", $contrainteOrientation);
$smarty->assign('show_rpu_consultation', $show_rpu_consultation);

$smarty->display("inc_aed_rpu");
