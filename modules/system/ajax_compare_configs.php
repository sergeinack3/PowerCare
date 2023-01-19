<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\System\CConfigurationCompare;

CCanDo::checkAdmin();

$upload_files = CValue::files('formfile');

CView::checkin();

if (!$upload_files) {
  CAppUI::commonError('CConfigurationCompare-error no file');
}

$comparator = new CConfigurationCompare();
$comparator->compare($upload_files);

$result = $comparator->getResult();

CMbArray::pluckSort($result, SORT_LOCALE_STRING, 'trad');

$files  = $comparator->getFilesNames();

$smarty = new CSmartyDP();
$smarty->assign('result', $result);
$smarty->assign('files_count', count($files) + 1);
$smarty->assign('main_file', $files[0]);
unset($files[0]);
$smarty->assign('files', $files);

$smarty->display('inc_compare_configs');
