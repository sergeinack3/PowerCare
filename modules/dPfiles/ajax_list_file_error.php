<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFileReport;
use Ox\Mediboard\Files\CFilesCategory;

CCanDo::checkAdmin();

$object_class      = CValue::get('object_class');
$error_type        = CValue::get('error_type');
$start             = (int)CValue::get('start', 0);

CView::enforceSlave();

$ds = CSQLDataSource::get('std');

$file_report = new CFileReport();

$where = array();
if ($object_class) {
  $where[] = "`file_report`.`object_class` = '$object_class'";
}

if ($error_type) {
  $where[] = "`file_report`.`$error_type`= '1'";
}

$limit = "{$start}, 50";

$ljoin = array();
$ljoin['files_mediboard'] = 'files_mediboard.file_real_filename = file_report.file_hash';
$total = $file_report->countList($where, null, $ljoin);

$request = new CRequest();
$request->addSelect('*');
$request->addTable('file_report');
$request->addLJoin($ljoin);
$request->addWhere($where);
$request->setLimit($limit);
$error_file_list = $ds->loadList($request->makeSelect());

$file_cat = new CFilesCategory();
$categories = $file_cat->loadList();

$smarty = new CSmartyDP();
$smarty->assign('error_type', $error_type);
$smarty->assign('object_class', $object_class);
$smarty->assign('error_file_list', $error_file_list);
$smarty->assign('file_report', $file_report);
$smarty->assign('file', new CFile());
$smarty->assign('start', $start);
$smarty->assign('total', $total);
$smarty->assign('categories', $categories);
$smarty->display('inc_list_error_files.tpl');