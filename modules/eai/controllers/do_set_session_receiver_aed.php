<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CValue;
use Ox\Core\CView;

$cn_receiver_guid = CView::post("cn_receiver_guid", "str");

if ($cn_receiver_guid == "none") {
  unset($_SESSION["cn_receiver_guid"]);
  CView::checkin();
  return;
}

CValue::setSessionAbs("cn_receiver_guid", $cn_receiver_guid);

CView::checkin();