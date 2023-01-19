<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Urgences\CRPU;

$rpu_id = CValue::get("rpu_id");

$rpu = new CRPU;
$rpu->load($rpu_id);

$smarty = new CSmartyDP;
$smarty->assign("rpu", $rpu);
$smarty->display("inc_edit_diag");
