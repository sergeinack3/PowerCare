<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CTemplateManagerExport;

CCanDo::checkRead();

$object_class = CView::get("object_class", "str");

CView::checkin();

if (!class_exists($object_class)) {
  CAppUI::stepAjax("CTemplateManager-Error fetching fields");
  return;
}

try {
  $export = new CTemplateManagerExport(new $object_class);
}
catch (Exception $e) {
  CAppUI::stepAjax($e->getMessage());
}

$export->export();
