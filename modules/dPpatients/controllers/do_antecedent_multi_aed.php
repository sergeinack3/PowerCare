<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CAntecedent;

$antecedent_ids = CView::post("antecedent_ids", "str");
CView::checkin();

$counter = 0;
$msg = null;

if ($antecedent_ids && count($antecedent_ids)) {
  foreach ($antecedent_ids as $_antecedent_id) {
    $atcd         = CAntecedent::find($_antecedent_id);
    $atcd->annule = 1;
    $msg          = $atcd->store();

    $counter++;
  }
}

CAppUI::displayMsg($msg, CAppUI::tr("CAntecedent-Canceled antecedents") . " x $counter");

echo CAppUI::getMsg();
CApp::rip();
