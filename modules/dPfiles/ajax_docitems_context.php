<?php 
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Files\CContextDoc;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$context_class = CView::get("context_class", "str");
$context_id    = CView::get("context_id", "ref class|$context_class");
$type          = CView::get("type", "str");

CView::checkin();

$context_doc = new CContextDoc();
$context_doc->context_class = $context_class;
$context_doc->context_id    = $context_id;
if ($type) {
  $context_doc->type = $type;
}
if (!$context_doc->loadMatchingObject()) {
  $context_doc->store();
}

$context = CMbObject::loadFromGuid("$context_class-$context_id");

switch ($context_class) {
  case "CProtocole":
    $object_class = $type === "sejour" ? "CSejour" : "COperation";
    $user_id = $context->chir_id;
    $function_id = $context->function_id;
    break;
  case "CProtocoleRPU":
    $object_class = "CSejour";
    $user_id = CMediusers::get()->_id;
    $function_id = null;
    break;
  default:
    $object_class = $context_class;
    $user_id = CMediusers::get()->_id;
    $function_id = null;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("context_doc" , $context_doc);
$smarty->assign("context"     , $context);
$smarty->assign("object_class", $object_class);
$smarty->assign("user_id"     , $user_id);
$smarty->assign("function_id" , $function_id);

$smarty->display("inc_docitems_context");