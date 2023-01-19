<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFileReport;

CCanDo::checkAdmin();

$object_class      = CValue::get('object_class');
$error_type        = CValue::get('error_type');

CView::enforceSlave();

$file_report = new CFileReport();

$smarty = new CSmartyDP();
$smarty->assign('file_report', $file_report);
$smarty->assign('file', new CFile());
$smarty->assign('object_class', $object_class);
$smarty->assign('error_type', $error_type);
$smarty->display('vw_list_error_files.tpl');