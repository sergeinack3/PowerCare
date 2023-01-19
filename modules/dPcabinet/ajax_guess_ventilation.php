<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$consult_id = CView::get("consult_id", "ref class|CConsultAnesth");
$patient_id = CView::getRefCheckEdit("patient_id", "ref class|CPatient");

CView::checkin();

$consult_anesth = new CConsultAnesth();
$consult_anesth->load($consult_id);


$patient = new CPatient();
$patient->load($patient_id);

$consts = $patient->loadRefLatestConstantes(null, array("poids", "taille"));
$constantes = reset($consts);


$auto_55 = false;
$auto_imc26 = false;
if ($patient->_annees) {
  $consult_anesth->plus_de_55_ans = $patient->_annees > 55 ? 1 : 0;
  $auto_55 = 1;
}

if ($constantes->_imc) {
  $consult_anesth->imc_sup_26 = $constantes->_imc > 26 ? 1 : 0;
  $auto_imc26 = 1;
}

$smarty = new CSmartyDP();
$smarty->assign("consult_anesth", $consult_anesth);
$smarty->assign("plus_de_55_ans", $auto_55);
$smarty->assign("imc_sup_26"    , $auto_imc26);
$smarty->display("inc_guess_ventilation");
