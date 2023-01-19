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
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$chir_id = CValue::get("chir_id");
$function_id = CValue::get("function_id");
$list_chir_ids = array();

$user = new CMediusers;
$user->load($chir_id);
$ds = $user->getDS();

if ($function_id) {
  $users = CConsultation::loadPraticiens(PERM_EDIT, $function_id);
  $list_chir_ids = array_keys($users);
}
else {
  $list_chir_ids = array($chir_id);
}

// Liste des consultations a avancer si desistement
$now = CMbDT::date();
$where = array(
  "plageconsult.date"           => " > '$now'",
  "plageconsult.chir_id"        => $ds->prepareIn($list_chir_ids),
  "consultation.si_desistement" => "= '1'",
  "consultation.annule"         => "= '0'",
);
$ljoin = array(
  "plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id",
);
$consultation_desist = new CConsultation();
/** @var CConsultation[] $consultations */
$consultations = $consultation_desist->loadList($where, "date, heure", null, null, $ljoin);

foreach ($consultations as $_consult) {
  $_consult->loadRefPatient();
  $_consult->loadRefPlageConsult();
  $_consult->loadRefCategorie();
  $_consult->loadRefPraticien()->loadRefFunction();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("consultations", $consultations);
$smarty->assign("function_id", $function_id);
$smarty->assign("user", $user);
$smarty->display("inc_list_consult_si_desistement.tpl");
