<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$now          = CMbDT::date();
$_date_min    = CValue::get("_date_min", "$now");
$_date_max    = CValue::get("_date_max", "$now");
$_telephone   = CValue::get("_telephone");
$_coordonnees = CValue::get("_coordonnees");
$_print_ipp   = CValue::get("_print_ipp", CAppUI::gconf("dPcabinet CConsultation show_IPP_print_consult"));
$sorting_mode = CValue::get('sorting_mode');
$chir_id      = CValue::getOrSession("chir");

$ds = CSQLDataSource::get('std');

$plage   = new CPlageconsult();
$consult = new CConsultation();
$patient = new CPatient();

$ljoin = array(
  "{$plage->_spec->table}"   => "`cs`.`plageconsult_id` = `{$plage->_spec->table}`.`{$plage->_spec->key}`",
  "{$patient->_spec->table}" => "`cs`.`patient_id` = `{$patient->_spec->table}`.`{$patient->_spec->key}`"
);

$practitioners                                            = CConsultation::loadPraticiens(PERM_EDIT);
$where                                                    = array();
$where["cs.annule"]                                       = "= '0'";
$where["{$plage->_spec->table}.date"]                     = $ds->prepare('BETWEEN ?1 AND ?2', $_date_min, $_date_max);
$where["{$plage->_spec->table}.chir_id"]                  = CSQLDataSource::prepareIn(array_keys($practitioners), $chir_id);
$where["{$patient->_spec->table}.{$patient->_spec->key}"] = 'IS NOT NULL';

$order_by = array();

$mysql_date_format = null;
$php_date_format   = null;
switch ($sorting_mode) {
  case 'day':
    $mysql_date_format = '%Y-%m-%d';
    $php_date_format   = '\\3';
    break;

  case 'month':
    $mysql_date_format = '%Y-%m';
    $php_date_format   = '\\2';
    break;

  case 'year':
    $mysql_date_format = '%Y';
    $php_date_format   = '\\1';
    break;

  default:
    CAppUI::stepAjax('common-error-Invalid parameter', UI_MSG_ERROR);
}

$order_by[] = "DATE_FORMAT(`{$patient->_spec->table}`.`naissance`, '$mysql_date_format')";
$order_by[] = "`{$patient->_spec->table}`.`nom`, `{$patient->_spec->table}`.`prenom`";

$request = new CRequest();
$request->addSelect('`cs`.*');
$request->addTable("{$consult->_spec->table} AS `cs`");
$request->addLJoin($ljoin);
$request->addWhere($where);
$request->addOrder($order_by);

$consultations = $consult->loadQueryList($request->makeSelect());

CStoredObject::massLoadFwdRef($consultations, 'plageconsult_id');
$patients = CStoredObject::massLoadFwdRef($consultations, 'patient_id');

if ($_print_ipp) {
  CPatient::massLoadIPP($patients);
}

$sorted_consults = array();

/** @var CConsultation $_consult */
foreach ($consultations as $_consult) {
  // Implicit loadRefPlageConsult()
  $_consult->loadRefPraticien();
  $_consult->loadRefPatient();

  $period = preg_replace('/(\d{4})-(\d{2})-(\d{2})/', $php_date_format, $_consult->_ref_patient->naissance);
  if (!isset($sorted_consults[$period])) {
    $sorted_consults[$period] = array();
  }

  $sorted_consults[$period][] = $_consult;
}
unset($consultations);

ksort($sorted_consults);

$smarty = new CSmartyDP();
$smarty->assign("consultations", $sorted_consults);
$smarty->assign("sorting_mode",  $sorting_mode);
$smarty->assign("date_min",      $_date_min);
$smarty->assign("date_max",      $_date_max);
$smarty->assign("show_phone",    $_telephone);
$smarty->assign("show_addr",     $_coordonnees);
$smarty->assign("show_IPP",      $_print_ipp);
$smarty->display("print_listing_consults.tpl");
