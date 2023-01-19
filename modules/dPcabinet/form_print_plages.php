<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CConsultationCategorie;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();
$now       = CMbDT::date();
$filter = new CConsultation;
$filter->_date_min  = CValue::get("_date_min" , $now);
$filter->_date_max  = CValue::get("_date_max" , $now);
$filter->_print_ipp = CValue::get("_print_ipp", CAppUI::gconf("dPcabinet CConsultation show_IPP_print_consult"));

$tomorrow  = CMbDT::date("+1 day", $now);
$week_deb  = CMbDT::date("last sunday", $now);
$week_fin  = CMbDT::date("next sunday", $week_deb);
$week_deb  = CMbDT::date("+1 day"     , $week_deb);

$rectif     = CMbDT::transform("+0 DAY", $now, "%d")-1;
$month_deb  = CMbDT::date("-$rectif DAYS", $now);
$month_fin  = CMbDT::date("+1 month", $month_deb);
$month_fin  = CMbDT::date("-1 day", $month_fin);

// Liste des praticiens
$listChir = CConsultation::loadPraticiens(PERM_EDIT);

//Liste des fonction
$group_id = CGroups::loadCurrent()->_id;
$fnc = new CFunctions();
$listFnc = $fnc->loadListWithPerms(PERM_READ, array("group_id" => " = '$group_id' "), 'text');
$mediuser = new CMediusers();
foreach ($listFnc as $id => $_fnc) {
  $users = $mediuser->loadProfessionnelDeSanteByPref(PERM_EDIT, $_fnc->_id, null, true);
  if (!count($users)) {
    unset($listFnc[$id]);
  }
}

// Chargement de toutes les categories
$categorie  = new CConsultationCategorie();
$categories = $categorie->loadList(null, "nom_categorie");

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("filter"            , $filter);
$smarty->assign("now"               , $now);
$smarty->assign("tomorrow"          , $tomorrow);
$smarty->assign("week_deb"          , $week_deb);
$smarty->assign("week_fin"          , $week_fin);
$smarty->assign("month_deb"         , $month_deb);
$smarty->assign("month_fin"         , $month_fin);
$smarty->assign("listChir"          , $listChir);
$smarty->assign("listFnc"           , $listFnc);
$smarty->assign("categories"        , $categories);

$smarty->display("form_print_plages");
