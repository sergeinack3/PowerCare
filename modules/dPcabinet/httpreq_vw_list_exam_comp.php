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
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;

CCanDo::checkEdit();

$selConsult  = CView::get("selConsult", "ref class|CConsultation");

CView::checkin();

$consult = new CConsultation();
$consult->load($selConsult);

CAccessMedicalData::logAccess($consult);

$consult->loadRefsExamsComp();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("consult", $consult);

$smarty->display("exam_comp.tpl");
