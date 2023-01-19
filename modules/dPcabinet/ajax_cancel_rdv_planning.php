<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;

$consultation_id = CView::getRefCheckEdit("consultation_id", "ref class|CConsultation", true);
CView::checkin();

$consultation = new CConsultation();
$consultation->load($consultation_id);

CAccessMedicalData::logAccess($consultation);

$smarty = new CSmartyDP();
$smarty->assign('consultation', $consultation);
$smarty->display("inc_cancel_rdv_planning.tpl");
