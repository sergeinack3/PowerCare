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
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocage;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * dPbloc
 */
CCanDo::checkEdit();

$blocage_id    = CView::get("blocage_id", 'ref class|CBlocage', true);
$date_replanif = CView::get("date_replanif", "date", true);
CView::checkin();

$date_min = CMbDT::format($date_replanif, "%Y-%m-01");
$date_max = CMbDT::date("-1 day", CMbDT::date("+1 month", $date_min));

$bloc = new CBlocOperatoire();
$where             = array();
$where["group_id"] = " = '" . CGroups::loadCurrent()->_id . "'";
$where["actif"]    = " = '1'";
/** @var CBlocOperatoire[] $blocs */
$blocs = $bloc->loadListWithPerms(PERM_READ, $where, "nom");

$blocages = array();
$salles   = array();

foreach ($blocs as $_bloc) {
  $salles[$_bloc->_id] = $_bloc->loadRefsSalles(array("actif" => "= '1'"));
  
  foreach ($salles[$_bloc->_id] as $_salle) {
    $blocage = new CBlocage();
    $whereBloc = array();
    $whereBloc["salle_id"] = "= '$_salle->_id'";
    $whereBloc[] = "deb <= '$date_max' AND fin >= '$date_min'";
    
    $blocages[$_salle->_id] = $blocage->loadList($whereBloc);
  }
}

$smarty = new CSmartyDP;

$smarty->assign("blocs"     , $blocs);
$smarty->assign("salles"    , $salles);
$smarty->assign("blocages"  , $blocages);
$smarty->assign("blocage_id", $blocage_id);
$smarty->assign("date_replanif", $date_replanif);
$smarty->assign("date_before", CMbDT::date("-1 month", $date_replanif));
$smarty->assign("date_after" , CMbDT::date("+1 month", $date_replanif));

$smarty->display("inc_list_blocages.tpl");
