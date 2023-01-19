<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CView;

$days_week = CView::post("days_week", "str");
$days      = explode("|", $days_week);

foreach ($days as $_day) {
  $_POST["day_week"] = $_day;
  $do                = new CDoObjectAddEdit("CPlageSeanceCollective", "plage_id");
  $do->doBind();
  $do->doStore();
}
CView::checkin();

echo CAppUI::getMsg();
CApp::rip();
