<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;

CCanDo::checkAdmin();

$licenses = array(
  "GNU GPL" => "GNU General Public License, see http://www.gnu.org/licenses/gpl.html",
  "OXOL"    => "OXOL, see http://www.mediboard.org/public/OXOL",
);

$spec = (new CModule())->getSpecs();

$smarty = new CSmartyDP();

$smarty->assign("licenses", $licenses);
$smarty->assign("categories_color", CModule::$category_color);
$smarty->assign("package_list", $spec["mod_package"]->_list);


$smarty->display("vw_create_module.tpl");
