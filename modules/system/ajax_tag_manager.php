<?php
/**
 * @package  Mediboard\System
 * @author   SAS OpenXtrem <dev@openxtrem.com>
 * @license  https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license  https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\CTag;

CCanDo::checkRead();

$object_class   = CValue::get("object_class");

$tag = new CTag();
$tag->canDo();

// smarty
$smarty = new CSmartyDP();
$smarty->assign("tag", $tag);
$smarty->assign("object_class", $object_class);
$smarty->assign("limit", 15);
$smarty->display("inc_tag_manager.tpl");
