<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CView;

CCanDo::checkEdit();
$groupe_days = CView::post("groupe_days", "str");

$days    = explode("|", $groupe_days);
$counter = 0;

foreach ($days as $_day) {
  $_POST["groupe_day"] = $_day;
  $do                  = new CDoObjectAddEdit("CPlageGroupePatient", "plage_groupe_patient_id");
  $do->doBind();
  $do->doStore();

  $counter++;
}
CView::checkin();

CAppUI::getMsg(true);
CAppUI::setMsg(CAppUI::tr("CPlageGroupePatient-msg-create") . " x $counter", UI_MSG_OK);

echo CAppUI::getMsg();
CApp::rip();
