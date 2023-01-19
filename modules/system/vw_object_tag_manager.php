<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\CTag;

CCanDo::checkRead();

$object_class = CValue::get("object_class");

$tree = CTag::getTree($object_class);

$smarty = new CSmartyDP("modules/system");
$smarty->assign("object_class", $object_class);
$smarty->assign("tree", $tree);
$smarty->assign("root", true);
$smarty->display("vw_object_tag_manager.tpl");