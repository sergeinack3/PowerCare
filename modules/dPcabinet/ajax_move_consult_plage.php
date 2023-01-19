<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;

CCanDo::checkAdmin();
$resolve  = CValue::get("resolve", 0);

//@todo supprimer la partie AMI après utilisation
$ljoin = array();
$ljoin["plageconsult"] = "plageconsult.plageconsult_id = consultation.plageconsult_id";
$ljoin["id_sante400"] = "consultation.consultation_id = id_sante400.object_id";

$where = array();
$where["id_sante400.object_class"] = " = 'CConsultation'";
$where["id_sante400.tag"] = " = 'ami_import'";
$where[] = "consultation.heure NOT BETWEEN plageconsult.debut AND plageconsult.fin
    AND consultation.heure <> '00:00:00'";
$where[] = "
  EXISTS ( SELECT * FROM plageconsult p
  WHERE p.chir_id = plageconsult.chir_id
  AND p.date = plageconsult.date
  AND consultation.heure BETWEEN p.debut AND p.fin
  )
";

$consultation = new CConsultation();
$nb_consult = $consultation->countList($where, null, $ljoin);
$limit = $resolve == 1 ? 100 : 30;
$consultations = $consultation->loadList($where, null, $limit, null, $ljoin);
CMbObject::massLoadFwdRef($consultations, "patient_id");
if (!$resolve) {
  CMbObject::massLoadFwdRef($consultations, "plageconsult_id");
}

foreach ($consultations as $consult) {
  $consult->loadRefPlageConsult();
  if (!$resolve) {
    $consult->loadRefPatient();
  }
}

if ($resolve) {
  foreach ($consultations as $consult) {
    $where = array();
    $where["chir_id"] = " = '".$consult->_ref_plageconsult->chir_id."'";
    $where["date"]    = " = '".$consult->_ref_plageconsult->date."'";
    $where[]   = " '$consult->heure' BETWEEN debut AND fin";

    $plage = new CPlageconsult();
    if ($plage->loadObject($where)) {
      $consult->plageconsult_id = $plage->_id;
      if ($msg = $consult->store()) {
        CApp::log("Log from ajax_move_consult_plage", $msg);
      }
    }
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("nb_consult"    , $nb_consult);
$smarty->assign("consultations" , $consultations);
$smarty->assign("resolve"       , $resolve);
$smarty->assign("limit"         , $limit);

$smarty->display("vw_move_consult_plage.tpl");




