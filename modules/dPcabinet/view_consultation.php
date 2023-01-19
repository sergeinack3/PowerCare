<?php
/**
 * @package Mediboard\Cabinet
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

CCanDo::checkRead();

$consultation_id = CValue::get("consultation_id");

$consultations = array();
$consultation = new CConsultation;
$consultation->load($consultation_id);

CAccessMedicalData::logAccess($consultation);

$consultation->loadRefsFwd();

$date = $consultation->_ref_plageconsult->date;

$prat = $consultation->_ref_plageconsult->_ref_chir;
$prat->loadRefs();

$patient = $consultation->_ref_patient;
$patient->loadRefs();
$patient_insnir = $patient->loadRefPatientINSNIR();
$patient_insnir->createDatamatrix($patient_insnir->createDataForDatamatrix());

$prat->loadRefFunction();
$chir_ids = array_keys($prat->_ref_function->loadRefsUsers());

$ds = $consultation->getDS();

// nexts rdvs for the same function
$ljoin = array("plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id");
$where = array();
$where["date"] = ">= '$date' ";
$where["patient_id"] = " = '$consultation->patient_id' ";
$where[] = "plageconsult.chir_id ".$ds->prepareIn($chir_ids)." OR plageconsult.remplacant_id ".$ds->prepareIn($chir_ids);
$where["annule"] = " != '1' ";
$where[$consultation->_spec->key] = " != '$consultation->_id'";
/** @var CConsultation[] $consultations */
$consultations = $consultation->loadList($where, "date ASC, heure ASC", null, null, $ljoin);
foreach ($consultations as $_consult) {
  $_consult->_ref_patient = $consultation->_ref_patient;
  $_consult->loadRefPraticien()->loadRefFunction();
  $_consult->_ref_plageconsult->loadRefRemplacant();
}


$today = CMbDT::date();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("consultation", $consultation);
$smarty->assign("consultations", $consultations);
$smarty->assign("patient"     , $patient      );
$smarty->assign("prat"        , $prat         );
$smarty->assign("today"       , $today        );

$smarty->display("view_consultation.tpl");
