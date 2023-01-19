<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CConsultationCategorie;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanningOp\CTypeAnesth;

if (CAppUI::pref('create_dhe_with_read_rights')) {
  CCanDo::checkRead();
}
else {
  CCanDo::checkEdit();
}

$object_id    = CView::get('object_id', 'str');
$object_class = CView::get('object_class', 'enum list|COperation|CConsultation');
$sejour_id    = CView::get('sejour_id', 'ref class|CSejour');
$patient_id   = CView::get('patient_id', 'ref class|CPatient');
$chir_id      = CView::get('chir_id', 'ref class|CMediusers');
$grossesse_id = CView::get('grossesse_id', 'ref class|CGrosesse');
$hour_urgence = CView::get("hour_urgence", "num");
$min_urgence  = CView::get("min_urgence", "num");
$date_urgence = CView::get("date_urgence", "date");
$salle_id     = CView::get("salle_id", "ref class|CSalle");

CView::checkin();

$groups = CGroups::loadGroups();
$group = CGroups::loadCurrent();

$user = CMediusers::get();

if ($object_id) {
  $object = CMbObject::loadFromGuid("$object_class-$object_id");
}
else {
  $object = new $object_class;
}

CAccessMedicalData::logAccess($object);

$patient = new CPatient();
if ($patient_id) {
  $patient->load($patient_id);
}

$chir = new CMediusers();
if ($chir_id) {
  $chir->load($chir_id);
}
$chir->loadRefFunction();

$sejour = new CSejour();
if ($sejour_id) {
  $sejour->load($sejour_id);
}

if ($object_class == 'COperation') {
  /** @var COperation $operation */
  $operation = $object;
  $operation->loadRefPlageOp();
  if ($operation->_id) {
    $operation->loadRefsDocItems();
    $operation->loadRefSalle();
    $operation->loadRefChir();
  }
  else {
    $operation->valueDefaults();
    $operation->sejour_id = $sejour_id;
    $operation->_ref_chir = $chir;
    $operation->chir_id = $chir->_id;
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
}
else {
  /** @var CConsultation $consultation */
  $consultation = $object;
  if ($consultation->_id) {
    $consultation->loadRefPlageConsult();
    $consultation->loadRefPraticien();
    $consultation->loadRefCategorie();
  }
  else {
    $consultation->sejour_id = $sejour_id;
    $consultation->patient_id = $patient_id;
    $consultation->_ref_patient = $patient;
    $consultation->_praticien_id = $chir_id;
    $consultation->_ref_praticien = $chir;
    $consultation->grossesse_id = $grossesse_id;
  }
}

$patient->loadRefsCorrespondants();

$smarty = new CSmartyDP();
$smarty->assign('user', $user);
$smarty->assign('sejour', $sejour);
$smarty->assign('object', $object);
$smarty->assign('patient', $patient);
$smarty->assign('chir', $chir);

if ($object->_class == 'COperation') {
  $types_anesth = new CTypeAnesth();
  $types_anesth = $types_anesth->loadGroupList();

  $anesthesistes = $user->loadAnesthesistes(PERM_READ);

  $blocs = CGroups::loadCurrent()->loadBlocs(PERM_READ);

  $smarty->assign("types_anesth"            , $types_anesth);
  $smarty->assign("anesthesistes"           , $anesthesistes);
  $smarty->assign("blocs"                   , $blocs);
  $smarty->assign("date_min"                , CMbDT::date());
  $smarty->assign("date_max"                , CMbDT::date("+".CAppUI::conf("dPplanningOp COperation nb_jours_urgence")." days", CMbDT::date()));
}
else {
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

  $smarty->assign('categories_consult'      , $categories);
  $smarty->assign('list_categories_consult' , $list_categories);
}

$smarty->display('dhe/inc_object');