<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CCategoryPrescription;
use Ox\Mediboard\Prescription\CFunctionCategoryPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\Ssr\CBilanSSR;

global $m;

CCanDo::checkRead();

$sejour_id = CView::get("sejour_id", "ref class|CSejour", true);

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$lines = array();

// Bilan SSR  
$bilan            = new CBilanSSR();
$bilan->sejour_id = $sejour->_id;
$bilan->loadMatchingObject();

// Prescription SSR
$prescription = $sejour->loadRefPrescriptionSejour();

$chapitre = $m === "psy" ? "psy" : "kine";

// Chargement des lignes de la prescription
if ($prescription->_id) {
  $line                  = new CPrescriptionLineElement();
  $line->prescription_id = $prescription->_id;
  $_lines                = $line->loadMatchingList("debut ASC");
  CStoredObject::massLoadBackRefs($_lines, "alerts", null, array("handled" => "= '0'"));
  foreach ($_lines as $_line) {
    $_line->getRecentModification();
    $lines[$_line->_ref_element_prescription->category_prescription_id][$_line->element_prescription_id][] = $_line;
  }
}

// Chargement des categories de prescription
$categories = array();
if (CModule::getActive("dPprescription")) {
  $category   = new CCategoryPrescription();
  $where[]    = "chapitre = '$chapitre'";
  $group_id   = CGroups::loadCurrent()->_id;
  $where[]    = "group_id = '$group_id' OR group_id IS NULL";
  $order      = "nom";
  $categories = $category->loadList($where, $order);
}

// Dossier médical visibile ?
$user                     = CMediusers::get();
$can_view_dossier_medical =
  CModule::getCanDo('dPcabinet')->edit ||
  CModule::getCanDo('dPbloc')->edit ||
  CModule::getCanDo('dPplanningOp')->edit ||
  $user->isFromType(array("Infirmière"));

$can_edit_prescription = $user->isPraticien() || $user->isAdmin();

// Suppression des categories vides
if (!$can_edit_prescription) {
  foreach ($categories as $_cat_id => $_category) {
    if (!array_key_exists($_cat_id, $lines)) {
      unset($categories[$_cat_id]);
    }
  }
}

// Possibilité de stopper les lignes pour les exécutants
$can_stop_lines = array();
foreach ($categories as $_categorie) {
  $can_stop_lines[$_categorie->_id] = $can_edit_prescription;
}

if ($user->isExecutantPrescription()) {
  $cat_ids = CMbArray::pluck($categories, "prescription_executant");
  CMbArray::removeValue("0", $cat_ids);

  $func_cat_presc = new CFunctionCategoryPrescription();
  $where          = array(
    "function_id"              => "= '$user->function_id'",
    "category_prescription_id" => CSQLDataSource::prepareIn(array_keys($cat_ids))
  );

  foreach ($func_cat_presc->loadList($where) as $_func_cat_presc) {
    $can_stop_lines[$_func_cat_presc->category_prescription_id] = $_func_cat_presc->_id;
  }
}

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("sejour", $sejour);
$smarty->assign("bilan", $bilan);
$smarty->assign("categories", $categories);
$smarty->assign("prescription", $prescription);
$smarty->assign("lines", $lines);
$smarty->assign("can_edit_prescription", $can_edit_prescription);
$smarty->assign("can_stop_lines", $can_stop_lines);
$smarty->display("inc_form_bilan_ssr");
