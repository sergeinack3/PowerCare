<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;

CCanDo::checkRead();

$consult_id = CValue::get("consult_id");
$consult = new CConsultation;
$consult->load($consult_id);

CAccessMedicalData::logAccess($consult);

$smarty = new CSmartyDP;
$smarty->assign("consult", $consult);

$smarty->display("inc_accident_travail.tpl");
