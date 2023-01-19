<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CObject;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFileIntegrity;
use Ox\Mediboard\Files\CFileReport;
use Ox\Mediboard\System\Cron\CCronJob;

CCanDo::checkAdmin();

$integrity = new CFileIntegrity();

$cron_job = new CCronJob();
$cron_job->name = 'integrity_files';

$cron_job->loadMatchingObjectEsc();

if (!$cron_job->_id) {
    $cron_job->execution = "0 * * * * *";
    $cron_job->params    = "m=files&dialog=ajax_cron_integrity_file";
    $cron_job->active    = 0;

    $cron_job->description = "Vérification d'intégrié des fichiers";

    $cron_job->store();
}

CView::enforceSlave();

$ds = CSQLDataSource::get('std');
$file_report = new CFileReport();

if ($ds->loadTable('file_report')) {
  $request = new CRequest();
  $request->addTable('file_report');
  $request->addSelect('DISTINCT object_class');
  $request->addOrder('object_class');
  $classes = $ds->loadColumn($request->makeSelect());

  $file_report->formatReportArray($classes);
  $file_report->getTotalErrorCount($classes);
}

$trash_file_count = 0;
$total_file_entries_count = 0;
$total_file_db_count = 0;
$file_count_by_class = array();
if ($ds->loadTable('file_entries')) {
  $request = new CRequest();
  $request->addTable('file_entries');
  $request->addWhereClause('file_path', "LIKE '%.trash'");
  $trash_file_count = $ds->loadResult($request->makeSelectCount());

  $request = new CRequest();
  $request->addTable('file_entries');
  $request->addWhereClause('file_path', "NOT LIKE '%.trash'");
  $total_file_entries_count = $ds->loadResult($request->makeSelectCount());

  $request = new CRequest();
  $request->addTable('files_mediboard');
  $total_file_db_count = $ds->loadResult($request->makeSelectCount());

  $file = new CFile();
  $group = array('object_class');
  $result = $file->countMultipleList(null, null, $group, null, $group);
  foreach ($result as $_result) {
    $file_count_by_class[$_result['object_class']] = $_result['total'];
  }
}

$request = new CRequest();
$request->addSelect('*');
$request->addTable('id_sante400');
$request->addWhereClause("tag", "= 'merged'");
$merged_object_count = $ds->loadResult($request->makeSelectCount());

$smarty = new CSmartyDP();
$smarty->assign("cron_job", $cron_job);
$smarty->assign('file_report', $file_report);
$smarty->assign('merged_object_count', $merged_object_count);
$smarty->assign('trash_file_count', $trash_file_count);
$smarty->assign('total_file_entries_count', $total_file_db_count);
$smarty->assign('total_file_db_count', $total_file_entries_count);
$smarty->assign('file_count_by_class', $file_count_by_class);
$smarty->assign('file_report_id', 0);
$smarty->display("vw_integrity_files.tpl");
