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

$consultation_id   = CValue::getOrSession("consultation_id");
$dossier_anesth_id = CValue::getOrSession("dossier_anesth_id");

// Chargement de la consultation
$consult = new CConsultation();
$consult->load($consultation_id);

CAccessMedicalData::logAccess($consult);

$consult->loadRefPlageConsult();
$consult->loadRefsFichesExamen();

$consult->_is_anesth = $consult->_ref_chir->isAnesth();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("consult"   , $consult);
$smarty->assign("dossier_anesth_id", $dossier_anesth_id);

$smarty->display("inc_examens_comp.tpl");
