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
use Ox\Mediboard\MonitoringPatient\CSupervisionInstantData;

CCanDo::checkAdmin();

$supervision_instant_data_id = CView::get("supervision_instant_data_id", "ref class|CSupervisionInstantData", true);

CView::checkin();

$instant_data = new CSupervisionInstantData();
$instant_data->load($supervision_instant_data_id);
$instant_data->loadRefsNotes();

if (!$instant_data->_id) {
  $instant_data->size = 11;
}

$smarty = new CSmartyDP();
$smarty->assign("instant_data", $instant_data);
$smarty->display("inc_edit_supervision_instant_data");
