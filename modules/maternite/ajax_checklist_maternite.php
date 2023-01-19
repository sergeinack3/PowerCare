<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\SalleOp\CDailyCheckList;
use Ox\Mediboard\System\CUserLog;

CCanDo::checkEdit();
$date = CView::get("date", "date", true);
CView::checkin();

// Les salles autorisées
$salle = new CSalle();
$ljoin = array("bloc_operatoire" => "sallesbloc.bloc_id = bloc_operatoire.bloc_operatoire_id");
$where = array("bloc_operatoire.type" => "= 'obst'");
/** @var CSalle[] $listSalles */
$listSalles = $salle->loadListWithPerms(PERM_READ, $where, null, null, null, $ljoin);
CStoredObject::massCountBackRefs($listSalles, 'check_list_type_links');

//Les dates de dernières validations
$date_last_checklist = array();
foreach ($listSalles as $salle) {
  if ($salle->cheklist_man && $salle->_count['check_list_type_links']) {
    $checklist               = new CDailyCheckList();
    $checklist->object_class = $salle->_class;
    $checklist->object_id    = $salle->_id;
    $checklist->loadMatchingObject("date DESC, date_validate DESC");
    if ($checklist->_id) {
      $log               = new CUserLog();
      $log->object_id    = $checklist->_id;
      $log->object_class = $checklist->_class;
      $log->loadMatchingObject("date DESC");
      $date_last_checklist[$salle->_id] = $log->date;
    }
    elseif ($checklist->date) {
      $date_last_checklist[$salle->_id] = $checklist->date;
    }
    else {
      $date_last_checklist[$salle->_id] = "";
    }
  }
}

$smarty = new CSmartyDP();

$smarty->assign("date_last_checklist", $date_last_checklist);
$smarty->assign("listSalles", $listSalles);
$smarty->assign("date", $date);

$smarty->display("vw_list_checklist_maternite.tpl");
