<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\System\ViewSender\CViewSender;

CCanDo::checkRead();

$plan_mode = CView::get("plan_mode", "enum list|production|sending default|production");
CView::checkin();

// Minute courante
$date_time = CMbDT::dateTime();
$minute = intval(CMbDT::transform($date_time, null, "%M"));
$day = intval(CMbDT::transform($date_time, null, "%d"));

// Chargement des senders
$sender = new CViewSender();

/** @var CViewSender[] $senders */
$senders = $sender->loadList(null, "name");
CStoredObject::massLoadBackRefs($senders, "sources_link");
foreach ($senders as $_sender) {
  $_sender->getActive($minute, null, $day);
  $_sender->makeHourPlan($plan_mode);
  $_sender->loadRefSendersSource();
}

// Tableau de charges
$hour_sum = array();
$hour_total = 0;
foreach (range(0, 59) as $min) {
  $hour_sum[$min] = 0;
  foreach ($senders as $_sender) {
    if ($_sender->active) {
      $hour_sum[$min] += $_sender->_hour_plan[$min];
      $hour_total += $_sender->_hour_plan[$min] / 60;
    }
  }
}

// Pas jour courant : jaune, jour courant : vert

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("plan_mode", $plan_mode);
$smarty->assign("senders", $senders);
$smarty->assign("hour_sum", $hour_sum);
$smarty->assign("hour_total", $hour_total);
$smarty->assign("date_time", $date_time);
$smarty->assign("minute", $minute);
$smarty->assign("day", $day);
$smarty->display("inc_list_view_senders.tpl");
