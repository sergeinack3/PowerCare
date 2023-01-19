<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

/**
 * List ccodable of patient.
 * If prat != codable->prat, disable the check
 */

$mod_name = CView::get("mod_name", "str");

if (!CModule::getActive('lifen') && !CModule::getActive('sas') && !$mod_name
  && !CModule::getCanDo('lifen')->edit && !CModule::getCanDo('sas')->edit) {
  CCanDo::checkEdit();
}

$patient_id  = CView::get("patient_id", "ref class|CPatient", true);
$object_guid = CView::get("object_guid", "str", true);
$prat_id     = CView::get("prat_id", "ref class|CMediusers");
$date_guess  = CView::get("date", "dateTime");  //datetime
$readonly    = CView::get("readonly", "bool default|0");
CView::checkin();

// patient
$patient = new CPatient();
$patient->load($patient_id);

// praticien
$praticien = new CMediusers();
$praticien->load($prat_id);

$where = array(
  "group_id" => "= '" . CGroups::loadCurrent()->_id . "'",
  "annule"   => "= '0'"
);

//sejours & opé
foreach ($patient->loadRefsSejours($where) as $_sejour) {
  $_sejour->loadRefPraticien();
  $_sejour->_guess_status = 0;
  $_sejour->loadRefsFiles();
  $_sejour->loadRefsDocs();

  if ($date_guess >= $_sejour->entree && $date_guess <= $_sejour->sortie) {
    //date matched
    $_sejour->_guess_status = 1;
    if ($_sejour->_ref_praticien->function_id == $praticien->function_id) {
      //function matched
      $_sejour->_guess_status = 2;
      if ($_sejour->_ref_praticien->_id == $prat_id) {
        //prat matched
        $_sejour->_guess_status = 3;
      }
    }
  }

  //consult de sejour
  foreach ($_sejour->loadRefsConsultations() as $_consult) {
    $_consult->getType();
    $_consult->loadRefPlageConsult();
    $_consult->loadRefPraticien()->loadRefFunction();
    $_consult->loadRefsFiles();
    $_consult->loadRefsDocs();
    $_consult->_guess_status = 0;

    if ($date_guess >= $_sejour->entree && $date_guess <= $_sejour->sortie) {
      //date matched
      $_consult->_guess_status = 1;
      if ($_consult->_ref_praticien->function_id == $praticien->function_id) {
        //function matched
        $_consult->_guess_status = 2;
        if ($_consult->_ref_praticien->_id == $prat_id) {
          //prat matched
          $_consult->_guess_status = 3;
        }
      }
    }
  }

  //interv du sejour
  foreach ($_sejour->loadRefsOperations(array("annulee" => "= '0'")) as $_operation) {
    $_operation->loadRefsFwd();
    $_operation->loadRefsFiles();
    $_operation->loadRefsDocs();

    if ($date_guess >= $_operation->debut_op && $date_guess <= $_operation->fin_op) {
      //date matched
      $_operation->_guess_status = 1;
      if ($_operation->_ref_praticien->function_id == $praticien->function_id) {
        //function matched
        $_operation->_guess_status = 2;
        if ($_operation->_ref_praticien->_id == $prat_id) {
          //prat matched
          $_operation->_guess_status = 3;
        }
      }
    }
  }
}

//consultations
foreach ($patient->loadRefsConsultations(array("annule" => "= '0'")) as $_consult) {
  $_consult->_guess_status = 0;
  if ($_consult->sejour_id) {
    unset($patient->_ref_consultations[$_consult->_id]);
    continue;
  }

  $function = $_consult->loadRefPraticien()->loadRefFunction();
  if ($function->group_id != CGroups::loadCurrent()->_id) {
    unset($patient->_ref_consultations[$_consult->_id]);
    continue;
  }

  $plage = $_consult->loadRefPlageConsult();
  if ($date_guess == $plage->date) {
    $_consult->_guess_status = 1;
    if ($function->_id != $praticien->function_id) {
      $_consult->_guess_status = 2;
      if ($_consult->_ref_praticien->_id == $prat_id) {
        $_consult->_guess_status = 3;
      }
    }
  }
  $_consult->loadRefsFiles();
  $_consult->loadRefsDocs();

  $_consult->getType();
  $_consult->loadRefPlageConsult();

  // Facture de consultation
  $facture = $_consult->loadRefFacture();
  if ($facture->_id) {
    $facture->loadRefsNotes();
  }
}

$smarty = new CSmartyDP();
$smarty->assign("patient", $patient);
$smarty->assign("object_guid", $object_guid);
$smarty->assign("readonly", $readonly);
$smarty->display("inc_list_refs_to_link.tpl");
