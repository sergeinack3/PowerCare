<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultAnesth;

CCanDo::checkRead();
$consultation_anesth_id = CView::get("consultation_anesth_id", "ref class|CConsultAnesth");
CView::checkin();

$consult_anesth = new CConsultAnesth();
$consult_anesth->load($consultation_anesth_id);

$score_met = $consult_anesth->loadRefScoreMet();

$smarty = new CSmartyDP();
$smarty->assign("consult_anesth", $consult_anesth);
$smarty->assign("score_met"     , $score_met);
$smarty->display("inc_vw_score_met");
