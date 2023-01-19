<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Files\CCSVImportFilesCategory;

CCanDo::checkEdit();

$file   = CValue::files("formfile");

CView::checkin();

if (!$file || !$file['tmp_name']) {
  CAppUI::stepAjax("CFile-not-exists", UI_MSG_ERROR, $file);
}

$import = new CCSVImportFilesCategory($file['tmp_name'][0]);
$import->import();

echo CAppUI::getMsg();