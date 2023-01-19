<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CTypeEvenementPatient;

CCanDo::checkRead();

$event_type_id = CView::get('event_type_id', 'ref class|CTypeEvenementPatient');

CView::checkin();

$notification = false;
if ($event_type_id) {
  $event_type = new CTypeEvenementPatient();
  $event_type->load($event_type_id);

  if ($event_type->notification && CModule::getActive('notifications')) {
    $notification = $event_type->loadRefNotification();
  }
}

$smarty = new CSmartyDP();
$smarty->assign('notification', $notification);
$smarty->display('inc_show_event_notification_infos.tpl');
