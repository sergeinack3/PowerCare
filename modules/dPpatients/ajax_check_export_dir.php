<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;

CCanDo::checkAdmin();

$directory = CView::get("directory", "str");
$prefix    = CView::get("prefix", "str");

CView::checkin();

if (!$directory) {
  return;
}

if (!is_writable($directory) || !is_dir($directory)) {
  CAppUI::stepAjax("mod-dPpatients-directory-unavailable", UI_MSG_ERROR);
}

$iterator = new DirectoryIterator($directory);
$count    = 0;

foreach ($iterator as $_fileinfo) {
  if ($_fileinfo->isDot()) {
    continue;
  }

  if ($prefix && strpos($_fileinfo->getFilename(), $prefix) === false) {
    continue;
  }

  $count++;
}

CAppUI::stepAjax("mod-dPpatients-directory-contains", UI_MSG_WARNING, $count);

