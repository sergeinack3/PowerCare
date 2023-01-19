<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Dicom\CDicomSession;

CCanDo::checkRead();

$_date_min = CValue::getOrSession('_date_min', CMbDT::dateTime("-7 day"));
$_date_max = CValue::getOrSession('_date_max', CMbDT::dateTime("+1 day"));

$session = new CDicomSession(null);

$session->_date_min = $_date_min;
$session->_date_max = $_date_max;

$smarty = new CSmartyDP();
$smarty->assign("session", $session);
$smarty->assign("page", 0);
$smarty->display("vw_idx_sessions.tpl");