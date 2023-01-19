<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$remplacant_id = CView::get("remplacant_id", "ref class|CMediusers");
$date          = CView::get("date", "dateTime default|now");
CView::checkin();
CView::enableSlave();

$user = new CMediusers();
$user->load($remplacant_id);

$remplace = $user->loadRefRemplace();

if (is_null($remplace)) {
  CApp::json(array());
  CApp::rip();
}
else {
  $data = "<em> est le remplacant de <span onmouseover=\"ObjectTooltip . createEx(this, '$remplace->_guid')\">" . $remplace->_view . "</span></em>";

  CApp::json($data);
}
