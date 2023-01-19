<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Création du template
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

$smarty = new CSmartyDP();

$smarty->assign("mod", CValue::get("mod"));

$smarty->display("module_missing.tpl");
