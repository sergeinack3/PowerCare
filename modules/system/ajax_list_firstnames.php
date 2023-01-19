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

$page = CValue::get('page', 0);

$name = trim(CValue::get('name'));
$type = CValue::get('type');

$first = new CFirstNameAssociativeSex();
$where = array();
if (trim($name)) {
  $where['firstname'] = " LIKE '%$name%' ";
}
if ($type) {
  $where['sex'] = " = '$type' ";
}

$nb_firsts = $first->countList($where);
$hundred_firsts = $first->loadList($where, "firstname", "$page,100");

// smarty
$smarty = new CSmartyDP();
$smarty->assign("list", $hundred_firsts);
$smarty->assign("total", $nb_firsts);
$smarty->assign("page", $page);
$smarty->display("inc_list_firstnames.tpl");