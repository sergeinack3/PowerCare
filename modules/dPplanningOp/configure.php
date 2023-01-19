<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

$hours = range(0, 23);
$minutes = range(0, 59);
$intervals = array("5", "10", "15", "20", "30");
$patient_ids = array("0", "1", "2");
$today = CMbDT::date();
$group = CGroups::loadCurrent();

// Nombre de patients
$where = array("entree" => ">= '$today 00:00:00'",
               "group_id" => "= '$group->_id'");
$sejour = new CSejour();
$nb_sejours = $sejour->countList($where);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("minutes"    , $minutes);
$smarty->assign("hours"      , $hours);
$smarty->assign("today"      , $today);
$smarty->assign("nb_sejours" , $nb_sejours);
$smarty->assign("intervals"  , $intervals);
$smarty->assign("patient_ids", $patient_ids);
$smarty->assign("group"            , $group);

$smarty->display("configure");
