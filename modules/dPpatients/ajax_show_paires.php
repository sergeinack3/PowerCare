<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CFirstNameAssociativeSex;

CCanDo::checkRead();

$lettre = CView::get("lettre", "str");

CView::checkin();
CView::enableSlave();

$paires = array();

if ($lettre) {
  $paire = new CFirstNameAssociativeSex();

  $where = array(
    "firstname" => "LIKE '$lettre%'"
  );

  $paires = $paire->loadList($where, "firstname");
}

$smarty = new CSmartyDP();

$smarty->assign("paires", $paires);
$smarty->assign("lettre", $lettre);

$smarty->display("inc_show_paires.tpl");