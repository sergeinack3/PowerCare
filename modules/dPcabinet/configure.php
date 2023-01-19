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
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkAdmin();

$hours = range(0, 23);
$intervals = array("05","10","15","20","30");

$function = new CFunctions();
$function->group_id = CGroups::loadCurrent()->_id;
$functions = $function->loadMatchingList("text");

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("hours"     , $hours);
$smarty->assign("date"      , CMbDT::date());
$smarty->assign("intervals" , $intervals);

$smarty->assign("debut"     , CMbDT::date("+ 5 YEAR"));
$smarty->assign("limit"     , "100");
$smarty->assign("praticiens", CMediusers::get()->loadPraticiens());
$smarty->assign("anesths"   , CMediusers::get()->loadAnesthesistes());
$smarty->assign("functions_id", $functions);
$smarty->assign("user"      , CUser::get());

$smarty->display("configure.tpl");
