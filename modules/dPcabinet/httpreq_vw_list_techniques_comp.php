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

$selConsult        = CValue::getOrSession("selConsult", 0);
$dossier_anesth_id = CValue::getOrSession("dossier_anesth_id", 0);

$consult = new CConsultation();
$consult->load($selConsult);

CAccessMedicalData::logAccess($consult);

$consult->loadRefConsultAnesth($dossier_anesth_id);
$consult->_ref_consult_anesth->loadRefsBack();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("consult_anesth", $consult->_ref_consult_anesth);

$smarty->display("inc_consult_anesth/techniques_comp.tpl");
