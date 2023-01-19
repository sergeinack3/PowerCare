<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\MonitoringPatient\CSupervisionTimedData;

CCanDo::checkAdmin();
$supervision_timed_data_id = CView::get("supervision_timed_data_id", "ref class|CSupervisionTimedData", true);
CView::checkin();

$timed_data = new CSupervisionTimedData();
$timed_data->load($supervision_timed_data_id);
$timed_data->loadRefsNotes();

$smarty = new CSmartyDP();
$smarty->assign("timed_data", $timed_data);
$smarty->display("inc_edit_supervision_timed_data");
