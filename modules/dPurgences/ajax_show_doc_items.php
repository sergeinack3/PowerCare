<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\PlanningOp\CSejour;

$consult_id = CValue::get("consult_id");
$sejour_id  = CValue::get("sejour_id");

$consult = new CConsultation();
$consult->load($consult_id);

CAccessMedicalData::logAccess($consult);

$sejour = new CSejour();
$sejour->load($sejour_id)->loadRefPatient()->loadRefPhotoIdentite();

CAccessMedicalData::logAccess($sejour);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("consult", $consult);
$smarty->assign("sejour", $sejour);

$smarty->display("inc_rpu_docitems");
