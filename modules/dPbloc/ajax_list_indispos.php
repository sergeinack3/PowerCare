<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CIndispoRessource;
use Ox\Mediboard\Bloc\CTypeRessource;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkEdit();

$indispo_ressource_id = CValue::getOrSession("indispo_ressource_id");
$date_indispo         = CValue::getOrSession("date_indispo", CMbDT::date());

$date_min = CMbDT::format($date_indispo, "%Y-%m-01");
$date_max = CMbDT::date("-1 day", CMbDT::date("+1 month", $date_min));

$group_id = CGroups::loadCurrent()->_id;

$type_ressource = new CTypeRessource();
$where = array("group_id" => "= '$group_id'");
/** @var CTypeRessource[] $types_ressources */
$types_ressources = $type_ressource->loadList($where);

$ressources = array();
$indispos   = array();

foreach ($types_ressources as $_type_ressource) {
  $ressources[$_type_ressource->_id] = $_type_ressource->loadRefsRessources();
  
  foreach ($ressources[$_type_ressource->_id] as $_ressource) {
    $indispo = new CIndispoRessource;
    $where = array();
    $where["deb"] = "<= '$date_max'";
    $where["fin"] = " >= '$date_min'";
    $where["ressource_materielle_id"] = "= '$_ressource->_id'";
    
    $indispos[$_ressource->_id] = $indispo->loadList($where);
  }
}

$smarty = new CSmartyDP;

$smarty->assign("ressources", $ressources);
$smarty->assign("indispos"  , $indispos);
$smarty->assign("types_ressources", $types_ressources);
$smarty->assign("date_indispo", $date_indispo);
$smarty->assign("prev_month", CMbDT::date("-1 month", $date_indispo));
$smarty->assign("next_month", CMbDT::date("+1 month", $date_indispo));
$smarty->assign("indispo_ressource_id", $indispo_ressource_id);

$smarty->display("inc_list_indispos.tpl");