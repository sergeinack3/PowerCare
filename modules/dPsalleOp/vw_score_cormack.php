<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultAnesth;

CCanDo::checkRead();
$consult_anesth_id = CView::get("consult_anesth_id", "ref class|CConsultAnesth");
CView::checkin();

$consult_anesth = new CConsultAnesth();
$consult_anesth->load($consult_anesth_id);

$consult_anesth->loadRefChir();

$smarty = new CSmartyDP();
$smarty->assign("consult_anesth", $consult_anesth);
$smarty->display("inc_vw_score_cormack");
