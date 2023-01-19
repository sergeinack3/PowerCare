<?php 
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Hl7\CHL7v2Message;

$ipp              = CView::get("ipp", "str");
$date_observation = CView::get("date_observation", "str");
$actor_guid       = CView::get("actor_guid", "str");
CView::checkin();

$actor = CMbObject::loadFromGuid($actor_guid);
$admits_found = CHL7v2Message::getAdmits($ipp, $actor, $date_observation);

$smarty = new CSmartyDP();
$smarty->assign("admits_found", $admits_found);
$smarty->assign("nda_message" , null);
$smarty->display("inc_admits_found.tpl");