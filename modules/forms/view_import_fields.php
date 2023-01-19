<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::checkAdmin();

$classes = array(
  "CExClass"           => "do_import_fields",
  "CExClassFieldGroup" => "do_import_groups",
);

$smarty = new CSmartyDP();
$smarty->assign("classes", $classes);
$smarty->display("view_import_fields.tpl");
