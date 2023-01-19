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
use Ox\Core\CView;
use Ox\Mediboard\Files\CFileImport;

CCanDo::checkAdmin();

$dir = rtrim(CAppUI::conf('dPfiles import_dir'), '/');

if (!$dir) {
  CAppUI::stepAjax('common-error-Missing parameter: %s', UI_MSG_ERROR, CAppUI::tr('config-dPfiles-import_dir-desc'));
}

if (!$user_id = CAppUI::conf('dPfiles import_mediuser_id')) {
  CAppUI::stepAjax('common-error-Missing parameter: %s', UI_MSG_ERROR, CAppUI::tr('User'));
}

$regex      = CView::post('regex', 'str', true);
$regex_date = CView::post('regex_date', 'str', true);
$import     = CView::post('import', 'str default|0');
$step       = CView::post('step', 'num default|50');
$start      = CView::post('start', 'num default|0');
$continue   = CView::post('continue', 'str default|0');
$file       = CView::post('file', 'str');

CView::setSession("regex", $regex);
CView::setSession("regex_date", $regex_date);

CView::checkin();

$step = abs($step);

if ($file) {
  $start = $start - $step;
}

if (!$regex) {
  CAppUI::stepAjax('common-error-Missing parameter: %s', UI_MSG_ERROR, CAppUI::tr('common-Regular expression'));
}

$file  = stripcslashes($file);
$regex = stripcslashes($regex);
$regex_date = stripcslashes($regex_date);
$regex = "/{$regex}/";
$regex_date = "/{$regex_date}/";

$file_import = new CFileImport($regex, $dir, $start, $step, $import, $regex_date);
$file_import->importFiles();

$sorted_files    = $file_import->getSortedFiles();
$sibling_objects = $file_import->getSiblings();
$related_objects = $file_import->getRelated();
$count_files     = $file_import->getCount();

$next = $start + $step;

if ($next > $count_files) {
  CAppUI::setMsg('common-msg-Importation done.', UI_MSG_OK);
}

echo CAppUI::getMsg();

$smarty = new CSmartyDP();
$smarty->assign('regex', $regex);
$smarty->assign('sorted_files', $sorted_files);
$smarty->assign('sibling_objects', $sibling_objects);
$smarty->assign('related_objects', $related_objects);
$smarty->assign('import', $import);
$smarty->assign('continue', $continue);
$smarty->assign('next', $next);
$smarty->assign('count', $count_files);
$smarty->display('inc_vw_file_matching_preview.tpl');
