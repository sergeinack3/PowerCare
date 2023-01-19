<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CExamIgs;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour_id = CView::get("sejour_id", "ref class|CSejour");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

if ($sejour && $sejour->_id) {
  $sejour->loadRefsExamsIGS();
  $sejour->loadLastChungScore();
  $sejour->loadLastExamGir();

  $igs = CExamIgs::getIGSFromList($sejour->_ref_exams_igs);

  $smarty = new CSmartyDP();
  $smarty->assign('sejour', $sejour);
  $smarty->assign('igs', $igs);
  $smarty->display('inc_vw_scores_digest');
}