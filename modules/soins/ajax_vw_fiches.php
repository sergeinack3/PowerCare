<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CExamIgs;
use Ox\Mediboard\PlanningOp\CSejour;

$sejour_id    = CValue::get('sejour_id');
$selected_tab = CView::get('selected_tab', 'str default|chung_score', true);
$digest       = CValue::get('digest');
CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

// Chargement des scores IGS
$sejour->loadRefsExamsIGS();
$sejour->loadRefsChungScore();
$sejour->loadRefsExamsGir();

$igs = CExamIgs::getIGSFromList($sejour->_ref_exams_igs);

$smarty = new CSmartyDP();
$smarty->assign("sejour", $sejour);
$smarty->assign('selected_tab', $selected_tab);
$smarty->assign('digest', $digest);
$smarty->assign('igs', $igs);
$smarty->display('inc_vw_fiches');
