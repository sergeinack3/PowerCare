<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFactureCoeff;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$user = CAppUI::$user;
$listPrat = array();
if($user->isPraticien()) {
  $listPrat = CConsultation::loadPraticiensCompta($user->user_id);
  $prat_id = CValue::getOrSession("prat_id", $user->_id);
}
else {
  $listPrat = CConsultation::loadPraticiensCompta();
  $prat_id = CValue::getOrSession("prat_id");
}

$prat = new CMediusers();
$prat->load($prat_id);
if($prat_id && !in_array($prat_id, array_keys($listPrat))) {
  $prat = new CMediusers();
}
$prat->loadRefFunction();

$coeff = new CFactureCoeff();
$coeffs = array();
if ($prat_id) {
  $where = array();
  $where["praticien_id"] = " = '$prat_id'";
  $coeffs = $coeff->loadGroupList($where);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("user"    , $user);
$smarty->assign("prat"    , $prat);
$smarty->assign("listPrat", $listPrat);
$smarty->assign("coeffs", $coeffs);

$smarty->display("vw_coeffs");