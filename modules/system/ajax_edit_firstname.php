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
use Ox\Mediboard\System\CFirstNameAssociativeSex;

CCanDo::checkEdit();

$fs = new CFirstNameAssociativeSex();
$fs_id = CValue::get('fs_id');
$fs->load($fs_id);

//smarty
$smarty = new CSmartyDP();
$smarty->assign("object", $fs);
$smarty->display("inc_edit_firstname.tpl");
