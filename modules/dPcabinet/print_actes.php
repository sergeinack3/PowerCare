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

CCanDo::checkEdit();

$consultation_id = CValue::get("consultation_id");

// Consultation courante
$consult = new CConsultation();
if ($consultation_id) {
  $consult->load($consultation_id);

  CAccessMedicalData::logAccess($consult);

  $consult->loadView();
  $consult->loadComplete();
  $consult->loadRefsActesNGAP();
  $consult->loadRefPraticien();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("consult", $consult);
$smarty->display("print_actes");
