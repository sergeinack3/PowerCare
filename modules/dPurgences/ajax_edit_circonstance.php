<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Urgences\CCirconstance;

$id = CValue::get("id");

$circonstance = new CCirconstance();
$circonstance->load($id);

$smarty = new CSmartyDP();
$smarty->assign("circonstance", $circonstance);
$smarty->display("inc_edit_circonstance");