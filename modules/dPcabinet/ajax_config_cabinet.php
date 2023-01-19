<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkAdmin();

$max_prat   = CValue::get("max_prat", 20);
$max_sec    = CValue::get("max_sec", 20);

//functions
$function = new CFunctions();
$praticien = new CMediusers();
$secretaire = new CMediusers();

$praticiens = array();
for ($a=1;$a<=$max_prat;$a++) {
  $praticiens[] = $praticien;
}

$secretaires = array();
for ($a=1;$a<=$max_sec;$a++) {
  $secretaires[] = $secretaire;
}

//profils
$profile = new CUser();
$profile->template = 1;

//profils prat
$profile->user_type = 13;
$profiles_medecin = $profile->loadMatchingList();

//profiles secretaire
$profile->user_type = 10;
$profiles_secretaire = $profile->loadMatchingList();

//no profile ? stop
if (!count($profiles_secretaire) || !count($profiles_medecin)) {
  CAppUI::stepAjax("No_profiles_yet", UI_MSG_ERROR);
}

//-------------------------------------------------
//smarty
$smarty = new CSmartyDP();
$smarty->assign("function", $function);

$smarty->assign("praticiens", $praticiens);
$smarty->assign("max_prat",  $max_prat);
$smarty->assign("profiles_prat",  $profiles_medecin);

$smarty->assign("secretaires", $secretaires);
$smarty->assign("max_sec",  $max_sec);
$smarty->assign("profiles_sec",  $profiles_secretaire);



$smarty->display("inc_config_cabinet.tpl");