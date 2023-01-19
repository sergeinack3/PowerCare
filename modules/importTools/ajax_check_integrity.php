<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Import\ImportTools\CExportIntegrityChecker;


CCanDo::checkAdmin();

$directory = CView::get("directory", "str notNull");
$step     = CView::get("step", "num default|10");
$start    = CView::get("start", "num");
$continue = CView::get("continue", "str default|0");

CView::checkin();

$directory = rtrim($directory, "/\\");
$file_path = "{$directory}/export.integrity";

$export_check = new CExportIntegrityChecker($directory, $file_path);
$export_check->checkExport($start, $step);

$stats = $export_check->getStats();

CAppUI::js("nextCheck({$stats['start']}, {$stats['total']})");

