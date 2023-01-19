<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Maternite\CNaissance;

$start      = CValue::get('start', CMbDT::date());
$end        = CValue::get('end', CMbDT::date());
$simulation = CValue::get('simulation');

$naissance             = new CNaissance();
$where                 = array();
$where[]               = "date_time BETWEEN '$start 00:00:00' AND '$end 23:59:59' ";
$where["by_caesarean"] = " != '1' ";

/** @var CNaissance[] $naissances */
$naissances = $naissance->loadList($where);

foreach ($naissances as $key => $_naissance) {
  $bloc = $_naissance->loadRefOperation()->loadRefSalle()->loadRefBloc();
  if ($bloc->_id && $bloc->type == 'obst') {
    unset($naissances[$key]);
    continue;
  }
}


$smarty = new CSmartyDP();
$smarty->assign("naissances", $naissances);
$smarty->assign("naissance", $naissance);
$smarty->display("inc_list_guess_naissance.tpl");