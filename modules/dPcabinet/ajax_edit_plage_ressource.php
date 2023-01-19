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
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CPlageRessourceCab;
use Ox\Mediboard\Cabinet\CRessourceCab;

CCanDo::checkEdit();

$plage_ressource_cab_id = CView::get("plage_ressource_cab_id", "ref class|CPlageRessourceCab");
$ressource_cab_id       = CView::get("ressource_cab_id", "ref class|CRessourceCab");
$function_id            = CView::getRefCheckEdit("function_id", "ref class|CFunctions", true);
$date                   = CView::get("date", "date default|now", true);

CView::checkin();

$plage = new CPlageRessourceCab();
$plage->load($plage_ressource_cab_id);

if ($plage->_id) {
  $plage->countDuplicatedPlages();
}
else {
  $plage->ressource_cab_id = $ressource_cab_id;
}

$ressource = new CRessourceCab();
$ressource->function_id = $function_id;

$ressources = $ressource->loadMatchingList("libelle");


$debut = CMbDT::date("last sunday", $date);
$debut = CMbDT::date("+1 day", $debut);

$list_days = array();
for ($i = 0; $i < 7; $i++) {
  $dateArr = CMbDT::date("+$i day", $debut);
  $list_days[$dateArr] = $dateArr;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("plage"     , $plage);
$smarty->assign("ressources", $ressources);
$smarty->assign("list_days" , $list_days);
$smarty->assign("holidays"  , CMbDT::getHolidays());
$smarty->assign("date"      , $date);

$smarty->display("inc_edit_plage_ressource.tpl");
