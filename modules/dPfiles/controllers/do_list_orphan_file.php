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
use Ox\Core\CMbPath;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFileReport;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkAdmin();

$start    = CValue::post('start');
$step     = CValue::post('step');
$repair   = CValue::post('repair');
$continue = CValue::post('continue');
$count    = 0;

if (!$step || $step < 1) {
  $step = 100;
}

if (!$start || $start < 0) {
  $start = 0;
}

$ds = CSQLDataSource::get('std');

$request = new CRequest();
$request->addSelect(array('id400', 'object_class', 'object_id'));
$request->addTable('id_sante400');
$request->addWhereClause("tag", "= 'merged'");
$request->addWhereClause('object_id', "!= id400");
$request->setLimit("{$start}, {$step}");
$merged_object_ids = $ds->loadList($request->makeSelect());

$error_list = array();

foreach ($merged_object_ids as $_merged_object) {
  // Get old object file path
  $sub_dir = $_merged_object['object_class'] . '/' .
    intval($_merged_object['id400']/1000) . '/' .
    $_merged_object['id400'];

  // Check for files
  $file_list = glob(CFile::getDirectory() . '/' . $sub_dir . '/*');
  if (count($file_list) > 0) {
    foreach (glob(CFile::getDirectory() . '/' . $sub_dir . '/*') as $_file_path) {
      $filename = basename($_file_path);
      // Ignore .trash files
      if (strpos($_file_path, '.trash') === false) {

        $error_list[$filename] = array(
          'object_class' => $_merged_object['object_class'],
          'object_id'    => $_merged_object['object_id'],
          'file_path'    => $_file_path
        );

        if ($repair) {
          // Get target dir
          $target_dir = CFile::getDirectory() . '/' .
            $_merged_object['object_class'] . '/' .
            intval($_merged_object['object_id'] / 1000) . '/' .
            $_merged_object['object_id'];

          // Get matching DB file
          $file                     = new CFile();
          $file->file_real_filename = $filename;
          $file->loadMatchingObject();

          // Get target object file
          $class = $_merged_object['object_class'];
          /** @var CStoredObject $object */
          $object = new $class();
          $object->load($_merged_object['object_id']);

          if ($file->_id && $object->_id) {
            // Actually move the file
            CMbPath::forceDir($target_dir);
            rename($_file_path, $target_dir . '/' . $filename);
            unset($error_list[$filename]);

            // Remove file_report entry
            $file_report = new CFileReport();
            $file_report->file_hash = $filename;
            $file_report->file_unfound = 1;
            if ($file_report->loadMatchingObject()) {
              $file_report->delete();
            }
          }
        }
      }
    }
  }
  $count++;
}

if ($repair) {
  CAppUI::js("\$V(getForm('populate_orphan_file').elements.continue, false);");
}

if (count($error_list) == 0 && !$repair) {
  $start += $step;

  if ($start && $count) {
    CAppUI::js("\$V(getForm('populate_orphan_file').elements.start, '$start');");
    CAppUI::js("submitPopulateForm(getForm('populate_orphan_file'));");
  }
}

$smarty = new CSmartyDP();
$smarty->assign('old_merged_objects', $error_list);
$smarty->assign('merged_error_count', count($error_list));
$smarty->assign('idext', new CIdSante400());
$smarty->assign('file', new CFile());
$smarty->display("inc_list_orphan_files.tpl");

CApp::rip();
