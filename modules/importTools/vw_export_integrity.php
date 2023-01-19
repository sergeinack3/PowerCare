<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkRead();

CView::checkin();

$root_dir = CAppUI::gconf("importTools export root_path");
$export_dirs = array();

$iterator = new DirectoryIterator($root_dir);
while ($dir = $iterator->getFilename()) {
  if ($iterator->isDot() || $iterator->isFile()) {
    $iterator->next();
    continue;
  }

  if (!isset($export_dirs[$dir])) {
    $export_dirs[$dir] = "";
  }

  $integrity_file = $iterator->getPathname() . '/export.integrity';
  if (is_file($integrity_file)) {
    $stats = file_get_contents($integrity_file);
    $export_dirs[$dir] = json_decode($stats, true);
  }

  $iterator->next();
}

$smarty = new CSmartyDP();
$smarty->assign("export_dirs", $export_dirs);
$smarty->assign("root_dir", rtrim($root_dir, '/\\'));
$smarty->display("vw_export_integrity");