<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour_id  = CValue::get("sejour_id");
$consult_id = CValue::get("consult_id");

$now = CMbDT::dateTime();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefRPU();
$rpu = $sejour->_ref_rpu;

$consult = new CConsultation();
$consult->load($consult_id);

CAccessMedicalData::logAccess($consult);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("now", $now);

$smarty->assign("sejour", $sejour);
$smarty->assign("consult", $consult);
$smarty->assign("rpu", $rpu);

$smarty->display("inc_sortie_reelle");
