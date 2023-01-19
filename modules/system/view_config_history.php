<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\System\CConfiguration;

CCanDo::check();

$feature      = CView::get("feature", "str");
$object_class = CView::get("object_class", "str class");
$object_id    = CView::get("object_id", "ref class|CMbObject meta|object_class");

CView::checkin();

$configuration = new CConfiguration();

$ds = $configuration->getDS();

$where = array(
  "feature" => $ds->prepare("=?", $feature),
);

if ($object_class && $object_id) {
  $where["object_class"] = $ds->prepare("=?", $object_class);
  $where["object_id"] = $ds->prepare("=?", $object_id);
}
else {
  $where["object_class"] = "IS NULL";
  $where["object_id"]    = "IS NULL";
}

$configuration->loadObject($where);

if ($configuration->_id) {
  CAppUI::redirect("m=system&a=view_history_object&object_class=$configuration->_class&object_id=$configuration->_id");
}
else {
  CAppUI::stepAjax("Aucun historique trouvé", UI_MSG_WARNING);
}