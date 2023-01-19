<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Sante400\CIncrementer;

CCanDo::checkAdmin();

$object_class = CValue::get("object_class");
if (!$object_class) {
  return;
}

$object = new $object_class;

$vars = array_keys(CIncrementer::getVars($object));
$vars = array_combine($vars, $vars);
foreach ($vars as &$_var) {
  $_var = "[$_var]";
}

$vars["VALUE"] = "%06d";

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("vars", $vars);
$smarty->display("inc_object_vars.tpl");