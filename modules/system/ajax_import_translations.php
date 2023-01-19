<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\System\CCSVImportTranslations;

CCanDo::checkAdmin();

$file = CValue::files('formfile');

CView::checkin();

if (!$file || !$file['tmp_name']) {
  CAppUI::commonError('common-error-No file found.');
}

$dir      = rtrim(CAppUI::conf('root_dir'), '/\\') . '/tmp';
$filename = "$dir/{$file['name'][0]}";

move_uploaded_file($file['tmp_name'][0], $filename);

$import = new CCSVImportTranslations($filename);
$import->parseFile();

$translations = $import->getTranslations();

$counts = $import->getNbHitsTotal();

$modules = array_keys(CModule::getInstalled());

sort($modules);

unlink($filename);

$smarty = new CSmartyDP();
$smarty->assign('translations', $translations);
$smarty->assign('modules', $modules);
$smarty->assign('file_name', basename($filename));
$smarty->assign('counts', $counts);
$smarty->display('inc_vw_import_translations');
