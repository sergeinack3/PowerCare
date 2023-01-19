<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CCSVImportPrestation;

CCanDo::checkAdmin();

$file   = CValue::files('formfile');
$dryrun = CView::post('dryrun', 'bool default|0');
$update = CView::post('update', 'bool default|0');

CView::checkin();

if (!$file || !$file['tmp_name']) {
  CAppUI::stepAjax("CFile-not-exists", UI_MSG_ERROR, $file);
}

$import = new CCSVImportPrestation($file['tmp_name'][0], $dryrun, $update);
$import->import();

$results = $import->getResults();

$smarty = new CSmartyDP();
$smarty->assign('results', $results);
$smarty->assign('dryrun', $dryrun);
$smarty->display('inc_import_prestation_csv.tpl');