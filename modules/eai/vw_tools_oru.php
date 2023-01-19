<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CModelObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$date_min  = CView::get('date_min', array('dateTime', 'default' => CMbDT::dateTime("-7 day")));
$date_max  = CView::get('date_max', array('dateTime', 'default' => CMbDT::dateTime("+1 day")));
CView::checkin();

$group_id = CGroups::loadCurrent()->_id;

$sender = new CInteropSender();
$senders = [];
foreach ($sender->getObjectsByClass('CSenderSOAP') as $_sender) {
    if ($_sender->group_id !== $group_id) {
        continue;
    }
    $senders[] = $_sender;
}
foreach ($sender->getObjectsByClass('CSenderFileSystem') as $_sender) {
    if ($_sender->group_id !== $group_id) {
        continue;
    }
    $senders[] = $_sender;
}
foreach ($sender->getObjectsByClass('CSenderFTP') as $_sender) {
    if ($_sender->group_id !== $group_id) {
        continue;
    }
    $senders[] = $_sender;
}

$smarty = new CSmartyDP();
$smarty->assign("exchange", new CExchangeDataFormat());
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("senders"  , $senders);
$smarty->display("vw_tools_oru.tpl");
