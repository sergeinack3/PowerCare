<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

$default_disposition = CValue::get("disposition", CAppUI::pref("mozaic_disposition", "2x2"));

$context = CValue::get("context_guid");

if ($context instanceof CSejour || $context instanceof COperation || $context instanceof CConsultation) {
  CAccessMedicalData::logAccess($context);
}

$doc = new CFile();
$doc->canDo();
if (!$doc->_can->edit) {
  CAppUI::stepAjax("pas le droit de créer un CFile", UI_MSG_ERROR);
}

if (!$context) {
  CAppUI::stepAjax("no_context_provided", UI_MSG_ERROR);
}
$context = CMbObject::loadFromGuid($context);
if (!$context->_id) {
  CAppUI::stepAjax("unexisting", UI_MSG_ERROR);
}
$context->canDo();
if (!$context->_can->read) {
  CAppUI::stepAjax("No right", UI_MSG_ERROR);
}

switch ($context->_class) {
  case 'CPatient':
    $patient = $context;
    break;

  case 'CSejour':
    /** @var CSejour $context */
    $patient = $context->loadRefPatient();
    break;

  case 'CConsultation':
  case 'CConsultAnesth':
    /** @var CConsultation $context */
    $patient = $context->loadRefPatient();
    break;

  case 'COperation':
    /** @var COperation $context */
    $patient = $context->loadRefPatient();
    break;

  case 'CEvenementPatient':
    /** @var CEvenementPatient $context */
    $patient = $context->loadRefPatient();
    break;

  default:
    $patient = new CPatient();
}

if (!$patient->_id) {
  CAppUI::stepAjax("CPatient.none", UI_MSG_ERROR);
}

$patient->loadRefsFiles();
foreach ($patient->_ref_files as $_key => $_file) {
  $right = $_file->canDo();
  if (!$_file->isImage() || !$_file->_can->read || $_file->annule) {
    unset($patient->_ref_files[$_key]);
    continue;
  }
}

/** @var CConsultation[] $consults */
$consults = $patient->loadRefsConsultations();
CMbObject::filterByPerm($consults, PERM_READ);
foreach ($consults as $_consult) {
  $_consult->loadRefsFiles();
  foreach ($_consult->_ref_files as $_key => $_file) {
    $right = $_file->canDo();
    if (!$_file->isImage() || !$_file->_can->read || $_file->annule) {
      unset($_consult->_ref_files[$_key]);
      continue;
    }
  }
}

$sejours  = $patient->loadRefsSejours();
CMbObject::filterByPerm($sejours, PERM_READ);
foreach ($sejours as $_sejour) {
  $_sejour->loadRefsFiles();
  foreach ($_sejour->_ref_files as $_key => $_file) {
    $right = $_file->canDo();
    if (!$_file->isImage() || !$_file->_can->read || $_file->annule) {
      unset($_sejour->_ref_files[$_key]);
      continue;
    }
  }

  $operations = $_sejour->loadRefsOperations();
  CMbObject::filterByPerm($operations);
  foreach ($operations as $_op) {
    $_op->loadRefsFiles();
    foreach ($_op->_ref_files as $_key => $_file) {
      $right = $_file->canDo();
      if (!$_file->isImage() || !$_file->_can->read || $_file->annule) {
        unset($_op->_ref_files[$_key]);
        continue;
      }
    }
  }
}

$patient->loadRefDossierMedical();
$events = $patient->_ref_dossier_medical->loadRefsEvenementsPatient();
CMbObject::filterByPerm($events);
foreach ($events as $event) {
  $event->loadRefsFiles();
  foreach ($event->_ref_files as $_key => $_file) {
    $right = $_file->canDo();
    if (!$_file->isImage() || !$_file->_can->read || $_file->annule) {
      unset($event->_ref_files[$_key]);
      continue;
    }
  }
}

// file categories
$category = new CFilesCategory();
$categories = $category->loadListWithPerms(PERM_EDIT);

$matrices = array();
$matrices["1x2"] = array("line" => 2, "col"=> 1);
$matrices["2x1"] = array("line" => 1, "col"=> 2);
$matrices["2x2"] = array("line" => 2, "col"=> 2);
$matrices["2x3"] = array("line" => 3, "col"=> 2);
$matrices["3x2"] = array("line" => 2, "col"=> 3);
$matrices["3x3"] = array("line" => 3, "col"=> 3);

$smarty = new CSmartyDP();
$smarty->assign("patient", $patient);
$smarty->assign("context", $context);
if (CModule::getActive("oxCabinet") && $context->_class === "CEvenementPatient") {
    $category = CAppUI::gconf("oxCabinet CEvenementPatient categorie_{$context->type}_default");
    $smarty->assign("category", $category);
}

$smarty->assign("matrices", $matrices);
$smarty->assign("categories", $categories);
$smarty->assign("default_disposition", $default_disposition);
$smarty->display("inc_img_to_document.tpl");
