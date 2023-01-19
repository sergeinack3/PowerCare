<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CGrossesse;

CCanDo::checkRead();
$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
CView::checkin();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);

$sejours       = $grossesse->loadRefsSejours();
$consultations = $grossesse->loadRefsConsultations(true);

CStoredObject::massLoadBackRefs($sejours, "consultations");
CStoredObject::massLoadBackRefs($sejours, "operations");

foreach ($consultations as $_cons) {
  $prat = $_cons->loadRefPraticien();
  $prat->loadRefFunction();
}

foreach ($sejours as $_sejour) {
  $_sejour->loadRefsConsultations();
  $_sejour->loadRefsOperations();
}

$smarty = new CSmartyDP();
$smarty->assign("grossesse", $grossesse);
$smarty->display("inc_list_grossesse_history");