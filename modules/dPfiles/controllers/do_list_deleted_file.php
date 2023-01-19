<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFileReport;

CCanDo::checkAdmin();

$file_report_id = CValue::post('file_report_id');
$step           = CValue::post('step');
$delete         = CValue::post('delete');
$continue       = CValue::post('continue');

if (!$step || $step < 1) {
  $step = 50;
}

$file_report = new CFileReport();

$where                   = array();
$where['db_unfound']     = "= '1'";
$where['file_report_id'] = "> '$file_report_id'";

$files_to_remove = $file_report->loadList($where, 'file_report_id', "{$step}");

if (!$files_to_remove) {
  CAppUI::js("\$V(getForm('populate_deleted_file').elements.continue, false);");
}
else {
  $last = end($files_to_remove);
  $file_report_id = $last->_id;
}

$db_present_count = 0;

if ($delete) {
  foreach ($files_to_remove as $_file) {
    $file                     = new CFile();
    $file->file_real_filename = $_file->file_hash;
    $file->loadMatchingObject();

    if ($file->_id) {
      $db_present_count++;
    }
    else {
      // Rename file
      rename($_file->file_path, "$_file->file_path.trash");

      // Delete file report entry
      $_file->delete();
    }
    $db_present_count == 0 ?
      CAppUI::setMsg("Fichier renommé", UI_MSG_OK) :
      CAppUI::setMsg("$db_present_count fichier(s) présent(s) en base", UI_MSG_ERROR);
  }
}

CAppUI::js("\$V(getForm('populate_deleted_file').elements.file_report_id, '$file_report_id');");
CAppUI::js("submitPopulateForm(getForm('populate_deleted_file'));");

echo CAppUI::getMsg();

$smarty = new CSmartyDP();
$smarty->assign('files_to_remove', $files_to_remove);
$smarty->assign('file', new CFile());
$smarty->display("inc_list_deleted_files.tpl");

CApp::rip();