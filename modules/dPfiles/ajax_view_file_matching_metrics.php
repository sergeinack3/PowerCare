<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkAdmin();

$dir = rtrim(CAppUI::conf('dPfiles import_dir'), '/') . '/';

if (!$dir) {
  CAppUI::stepAjax('common-error-Missing parameter: %s', UI_MSG_ERROR, CAppUI::tr('config-dPfiles-import_dir-desc'));
}

if (!$user_id = CAppUI::conf('dPfiles import_mediuser_id')) {
  CAppUI::stepAjax('common-error-Missing parameter: %s', UI_MSG_ERROR, CAppUI::tr('User'));
}

$regex = CValue::post('regex');

if (!$regex) {
  CAppUI::stepAjax('common-error-Missing parameter: %s', UI_MSG_ERROR, CAppUI::tr('common-Regular expression'));
}

$regex = stripcslashes($regex);
$regex = "/{$regex}/";

$all_files   = glob("{$dir}*");
$count_files = count($all_files);

$parsed_files   = array();
$unparsed_files = array();

$related_objects = array();
$sibling_objects = array();

// Good and bad files
foreach ($all_files as $_file) {
  $_filename = basename($_file);

  $_cfile            = new CFile();
  $_cfile->file_name = $_filename;
  $_cfile->author_id = $user_id;
  $_cfile->loadMatchingObjectEsc();

  if ($_cfile && $_cfile->_id) {
    $related_objects[$_filename] = $_cfile;
  }

  if (preg_match($regex, $_filename, $_match)) {
    $parsed_files[$_filename] = $_match;
  }
  else {
    $unparsed_files[] = $_filename;
  }
}

// Good files are analyzed
$sorted_files = array();
foreach ($parsed_files as $_filename => $_file) {
  $sorted_files[$_filename] = array();

  foreach ($_file as $_field => $_value) {
    if (is_string($_field)) {
      $sorted_files[$_filename][$_field] = $_value;
    }
  }
}

// Object loading of analyzed files
$sibling_objects = array();
foreach ($sorted_files as $_filename => $_file) {
  $_patient = new CPatient();

  if (isset($_file['IPP']) && $_file['IPP']) {
    $_patient->_IPP = $_file['IPP'];

    $_patient->loadFromIPP();

    if ($_patient && $_patient->_id) {
      $sibling_objects[$_filename] = $_patient;
      continue;
    }
  }

  if (isset($_file['lastname']) && $_file['lastname']) {
    $_patient->nom = str_replace('_', ' ', $_file['lastname']);
  }

  if (isset($_file['firstname']) && $_file['firstname']) {
    $_patient->prenom = str_replace('_', ' ', $_file['firstname']);
  }

  if (isset($_file['birthdate']) && $_file['birthdate']) {
    $_patient->naissance = preg_replace('/^(\d{4})(\d{2})(\d{2})$/', '\\1-\\2-\\3', $_file['birthdate']);
  }

  $_patient->loadMatchingObjectEsc();

  if ($_patient && $_patient->_id) {
    $sibling_objects[$_filename] = $_patient;
  }
}

$smarty = new CSmartyDP();
$smarty->assign('regex', $regex);
$smarty->assign('parsed_files', $parsed_files);
$smarty->assign('unparsed_files', $unparsed_files);
$smarty->assign('sibling_objects', $sibling_objects);
$smarty->assign('related_objects', $related_objects);
$smarty->assign('count', $count_files);
$smarty->display('inc_vw_file_matching_metrics.tpl');
