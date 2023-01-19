<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::check();

$object_class = CView::get("object_class", "str");
$object_id    = CView::get("object_id", "ref class|$object_class");

CView::checkin();

if (!$object_class && !$object_id) {
  return;
}

$object = new $object_class();
if ($object->load($object_id)) {
  $object->loadRefsNotes(PERM_READ);
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("object", $object);
$smarty->assign("mode"  , "edit");
$smarty->assign("float" , "left");
$smarty->display("inc_get_notes_image.tpl");
