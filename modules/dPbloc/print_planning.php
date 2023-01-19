<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * dPbloc
 */
CCanDo::checkRead();

$now = CMbDT::date();

$filter = new COperation();
$filter->_datetime_min = CView::get("_datetime_min", "dateTime");
$filter->_datetime_max = CView::get("_datetime_max", "dateTime");
$filter->_prat_id      = CView::get("_prat_id", 'ref class|CMediusers', true);
$filter->salle_id      = CView::get("salle_id", 'str', true);
$filter->_plage        = CView::get("_plage", 'bool default|' . CAppUI::gconf("dPbloc printing plage_vide"), true);
$filter->_ranking      = CView::get("_ranking", 'enum list|ok|ko', true);
$filter->_specialite   = CView::get("_specialite", 'ref class|CFunctions', true);
$filter->_codes_ccam   = CView::get("_codes_ccam", 'str', true);
$filter->_ccam_libelle = CView::get("_ccam_libelle", 'str default|' . CAppUI::gconf("dPbloc printing libelle_ccam"), true);
$filterSejour = new CSejour();
$filterSejour->type = CView::get("type", 'enum list|' . implode("|", CSejour::$types), true);

CView::checkin();

if (!$filter->_datetime_min || !$filter->_datetime_max) {
  // Récupération en session de la date éventuellement présente de l'onglet Hors plage
  if (isset($_SESSION["dPbloc"]["date"])) {
    $filter->_datetime_min = $_SESSION["dPbloc"]["date"] . " 00:00:00";
    $filter->_datetime_max = $_SESSION["dPbloc"]["date"] . " 23:59:59";
  }
  else {
    $filter->_datetime_min = "$now 00:00:00";
    $filter->_datetime_max = "$now 23:59:59";
  }
}

$tomorrow  = CMbDT::date("+1 day", $now);
$j2        = CMbDT::date("+2 day", $now);
$j3        = CMbDT::date("+3 day", $now);

$week_deb  = CMbDT::date("last sunday", $now);
$week_fin  = CMbDT::date("next sunday", $week_deb);
$week_deb  = CMbDT::date("+1 day"     , $week_deb);

$next_week_deb = CMbDT::date("+1 day"     , $week_fin);
$next_week_fin = CMbDT::date("next sunday", $next_week_deb);

$rectif     = CMbDT::transform("+0 DAY", $now, "%d")-1;
$month_deb  = CMbDT::date("-$rectif DAYS", $now);
$month_fin  = CMbDT::date("+1 month", $month_deb);
$month_fin  = CMbDT::date("-1 day", $month_fin);

$next_month_deb = CMbDT::date("+1 day", $month_fin);
$next_month_fin = CMbDT::date("+1 month", $month_fin);
$next_month_fin = CMbDT::date("-1 day", $next_month_fin);

$listPrat = new CMediusers();
$listPrat = $listPrat->loadPraticiens(PERM_READ);

$listSpec = new CFunctions();
$listSpec = $listSpec->loadSpecialites(PERM_READ, 1);

$bloc = new CBlocOperatoire();
$group = CGroups::loadCurrent();
$where             = array();
$where["group_id"] = "= '$group->_id'";
$where["actif"]    = "= '1'";
/** @var CBlocOperatoire[] $listBlocs */
$listBlocs = $bloc->loadListWithPerms(PERM_READ, $where, "nom");
foreach ($listBlocs as &$bloc) {
  $bloc->loadRefsSalles(array("actif" => "= '1'"));
}

$praticien = CMediusers::get();
// Création du template
$smarty = new CSmartyDP("modules/dPbloc");

$smarty->assign("praticien"    , $praticien);
$smarty->assign("chir"         , $praticien->user_id);
$smarty->assign("filter"       , $filter);
$smarty->assign("filterSejour" , $filterSejour);
$smarty->assign("now"          , $now);
$smarty->assign("tomorrow"     , $tomorrow);
$smarty->assign("j2"           , $j2);
$smarty->assign("j3"           , $j3);
$smarty->assign("next_week_deb", $next_week_deb);
$smarty->assign("next_week_fin", $next_week_fin);
$smarty->assign("week_deb"     , $week_deb);
$smarty->assign("week_fin"     , $week_fin);
$smarty->assign("month_deb"    , $month_deb);
$smarty->assign("month_fin"    , $month_fin);
$smarty->assign("next_month_deb", $next_month_deb);
$smarty->assign("next_month_fin", $next_month_fin);
$smarty->assign("listPrat"     , $listPrat);
$smarty->assign("listSpec"     , $listSpec);
$smarty->assign("listBlocs"    , $listBlocs);

$smarty->display("print_planning");
