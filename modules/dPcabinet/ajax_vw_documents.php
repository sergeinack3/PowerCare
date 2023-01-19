<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;

CCanDo::checkRead();

$consult_id        = CView::getRefCheckRead("consult_id", "ref class|CConsultation");
$dossier_anesth_id = CView::get("dossier_anesth_id", "ref class|CConsultAnesth");
$only_consult      = CView::get("only_consult", "bool default|0");

CView::checkin();

$consult = new CConsultation();
$consult->load($consult_id);

CAccessMedicalData::logAccess($consult);

$consult->loadRefConsultAnesth($dossier_anesth_id);
if ($consult->_ref_consult_anesth->_id) {
  $consult->_ref_consult_anesth->loadRefSejour();
}
$consult->loadRefPlageConsult();
$consult->loadRefSejour();
$consult->loadRefPatient();
$consult->loadRefPraticien();

if (CModule::getActive("appFineClient") && CAppUI::gconf("appFineClient Sync allow_appfine_sync")) {
  CAppFineClient::loadIdex($consult->_ref_patient);
  CAppFineClient::loadIdex($consult);
  $consult->_ref_patient->loadRefStatusPatientUser();

  $count_order_no_treated = CAppFineClient::countOrderNotTreated($consult, ['CFile', 'CCompteRendu']);
}

$smarty = new CSmartyDP();

$smarty->assign("consult"     , $consult);
$smarty->assign("only_consult", $only_consult);
$smarty->assign("count_order", $count_order_no_treated ?? 0);

$smarty->display("inc_fdr_consult.tpl");
