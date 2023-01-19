<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CTechniqueComp;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CTypeAnesth;

CCanDo::checkRead();

$ds = CSQLDataSource::get("std");

$op    = CView::get("op", 'ref class|COperation', true);
$date  = CView::get("date", 'date default|now', true);

CView::checkin();

$consultAnesth  = new CConsultAnesth();
$consult        = new CConsultation();
$userSel        = new CMediusers();
$operation      = new COperation();
$operation->load($op);

CAccessMedicalData::logAccess($operation);

$operation->loadRefChir();
$operation->loadRefSejour();
$consult_anesth = $operation->loadRefsConsultAnesth();

if ($consult_anesth->_id) {
  $consult_anesth->loadRefConsultation();
  $consult = $consult_anesth->_ref_consultation;
  $consult->_ref_consult_anesth = $consultAnesth;
  $consult->loadRefPlageConsult();
  $consult->loadRefsDocItems();
  $consult->loadRefPatient();
  $prat_id = $consult->_ref_plageconsult->chir_id;

  $consult_anesth->loadRefs();

  // On charge le praticien
  $userSel->load($prat_id);
  $userSel->loadRefs();
}

$anesth = new CTypeAnesth();
$anesth = $anesth->loadGroupList();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("op"             , $op);
$smarty->assign("date"           , $date);
$smarty->assign("operation"      , $operation);
$smarty->assign("anesth"         , $anesth);
$smarty->assign("techniquesComp" , new CTechniqueComp());
$smarty->assign("isPrescriptionInstalled", CModule::getActive("prescription"));
$smarty->display("vw_anesthesie");
