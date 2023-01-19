<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;

CCanDo::checkRead();

$plage_id   = CView::get('plage_id', 'ref class|CPlageconsult');
$consult_id = CView::get('consult_id', 'ref class|CConsultation');

CView::checkin();

$frequency = 15;
if ($plage_id) {
  $plage = new CPlageconsult();
  $plage->load($plage_id);
  $frequency = $plage->_freq_minutes;
}

$consult = new CConsultation();
if ($consult_id) {
  $consult->load($consult_id);

  CAccessMedicalData::logAccess($consult);
}

$smarty = new CSmartyDP();
$smarty->assign('frequency', $frequency);
$smarty->assign('consult', $consult);
$smarty->display('dhe/consultation/inc_edit_duree');