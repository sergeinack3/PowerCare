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
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFileReport;

CCanDo::checkAdmin();

$file_report_id = CValue::post('file_report_id');
$step           = CValue::post('step');
$repair         = CValue::post('repair');
$continue       = CValue::post('continue');

if (!$step || $step < 1) {
  $step = 50;
}

$ds                   = CSQLDataSource::get('std');
$size_corrected_count = 0;

$file_report = new CFileReport();

$where                   = array();
$where['size_mismatch']  = "= '1'";
$where['file_report_id'] = "> '$file_report_id'";

$size_mismatch_list = $file_report->loadList($where, 'file_report_id', "{$step}");

if (!$size_mismatch_list) {
  CAppUI::js("\$V(getForm('populate_file_size').elements.continue, false);");
}
else {
  $last = end($size_mismatch_list);
  $file_report_id = $last->_id;
}

if ($repair) {
  foreach ($size_mismatch_list as $_file) {
    $file = new CFile();
    $file->file_real_filename = $_file->file_hash;
    $file->loadMatchingObject();

    if ($file->_id && ($file->doc_size != $_file->file_size)) {
      $file->doc_size = $_file->file_size;
      if (!$msg = $file->store()) {
        $_file->delete();
        CAppUI::setMsg("Fichier corrigé", UI_MSG_OK);
      }
    }
  }
}

CAppUI::js("\$V(getForm('populate_file_size').elements.file_report_id, '$file_report_id');");
CAppUI::js("submitPopulateForm(getForm('populate_file_size'));");

echo CAppUI::getMsg();

$smarty = new CSmartyDP();
$smarty->assign('size_mismatch_list', $size_mismatch_list);
$smarty->assign('file', new CFile());
$smarty->display("inc_list_size_mismatch_file.tpl");

CApp::rip();