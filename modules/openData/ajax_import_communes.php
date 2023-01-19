<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\OpenData\CCommuneImport;

CCanDo::checkEdit();

$start      = CView::get('start', 'num default|0');
$step       = CView::get('step', 'num default|100 min|0');
$version    = CView::get('version', 'enum list|' . implode('|', array_keys(CCommuneImport::$versions_france)) . ' notNull');
$continue   = CView::get('continue', 'bool default|0');
$import_all = CView::get('import_all', 'bool default|0');
$update     = CView::get('update', 'bool default|0');
$zip        = CView::get('zip', 'bool default|0');

CView::checkin();

$step = ($import_all) ? 0 : $step;

$version = CCommuneImport::$versions_france[$version];

/** @var CCommuneImport $commune_import */
$commune_import = new $version();
$commune_import->getFile($zip);

$last_id = $commune_import->importFile($start, $step, $update);

if ($last_id) {
  CAppUI::js("\$V(getForm('import-communes-france').elements.start, '$last_id')");
  if ($continue) {
    CAppUI::js("ImportCommunes.nextImportCommunesFrance()");
  }
}
else {
  $commune_import->removeFile();
}

echo CAppUI::getMsg();