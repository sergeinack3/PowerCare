<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;

CCanDo::checkEdit();

$salle_id = CView::get("salle_id", 'ref class|CSalle');
$deb      = CView::get("deb", 'dateTime');
$fin      = CView::get("fin", 'dateTime');

CView::checkin();

if ($deb > $fin) {
  list($deb, $fin) = array($fin, $deb);
}

$plage = new CPlageOp();

$where = array();

$where["salle_id"] = "= '$salle_id'";
$where[] = "CONCAT(date, ' ', debut) BETWEEN '$deb' AND '$fin' OR CONCAT(date, ' ', fin) BETWEEN '$deb' AND '$fin'";

/** @var CPlageOp[] $plages */
$plages = $plage->loadList($where);
$plages_with_interv = array();
$plages_to_edit = array();
foreach ($plages as $_key => $_plage) {
  $_debut = $_plage->date . ' ' . $_plage->debut;
  $_fin = $_plage->date . ' ' . $_plage->fin;

  if ($_plage->countBackRefs("operations") > 0) {
    $_plage->loadRefsOperations();
    $plages_with_interv[$_key] = $_plage;
    unset($plages[$_key]);
  }
  elseif ($_debut < $deb || $_fin > $fin) {
    $plages_to_edit[$_key] = $_plage;
    unset($plages[$_key]);
  }
}

$smarty = new CSmartyDP;

$smarty->assign("plages_to_delete", $plages);
$smarty->assign('plages_to_edit', $plages_to_edit);
$smarty->assign('plages_with_interv', $plages_with_interv);

$smarty->display("inc_list_plages_to_delete.tpl");
