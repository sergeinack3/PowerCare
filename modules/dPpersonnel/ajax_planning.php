<?php
/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

//CCanDo::checkRead();

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPlageConge;
use Ox\Mediboard\Personnel\CRemplacement;

$choix              = CView::get("choix", "str default|mois");
$type_view          = CView::get("type_view", "enum list|conge|remplacement default|conge");
$filter             = new CPlageConge();
$filter->user_id    = CView::get("user_id", "ref class|CMediusers default|" . CAppUI::$user->_id);
$filter->date_debut = CView::get("date_debut", "date default|now");
CView::checkin();

// Tableau des jours fériés sur 2 ans, car
// en mode semaine : 31 décembre - 1 janvier
$bank_holidays = array_merge(
  CMbDT::getHolidays($filter->date_debut),
  CMbDT::getHolidays(CMbDT::transform("+1 YEAR", $filter->date_debut, "%Y-%m-%d"))
);

$mediuser  = new CMediusers();
$mediusers = $mediuser->loadListFromType();

if (!$filter->date_debut) {
  $filter->date_debut = CMbDT::date();
}

// Si la date rentrée par l'utilisateur est un lundi,
// on calcule le dimanche d'avant et on rajoute un jour. 
$tab_start = array();
if ($choix == "semaine") {
  $last_sunday   = CMbDT::transform('last sunday', $filter->date_debut, '%Y-%m-%d');
  $last_monday   = CMbDT::transform('+1 day', $last_sunday, '%Y-%m-%d');
  $debut_periode = $last_monday;

  $fin_periode = CMbDT::transform('+6 day', $debut_periode, '%Y-%m-%d');
} elseif ($choix == "annee") {
  list($year, $m, $j) = explode("-", $filter->date_debut);
  $debut_periode = "$year-01-01";
  $fin_periode   = "$year-12-31";
  $j             = 1;
  for ($i = 1; $i < 13; $i++) {
    if (!date("w", mktime(0, 0, 0, $i, 1, $year))) {
      $tab_start[$j] = 7;
    } else {
      $tab_start[$j] = date("w", mktime(0, 0, 0, $i, 1, $year));
    }
    $j++;
    $tab_start[$j] = date("t", mktime(0, 0, 0, $i, 1, $year));
    $j++;
  }
} else {
  list($a, $m, $j) = explode("-", $filter->date_debut);
  $debut_periode = "$a-$m-01";
  $fin_periode   = CMbDT::transform('+1 month', $debut_periode, '%Y-%m-%d');
  $fin_periode   = CMbDT::transform('-1 day', $fin_periode, '%Y-%m-%d');
}

$tableau_periode = array();
for ($i = 0; $i < CMbDT::daysRelative($debut_periode, $fin_periode) + 1; $i++) {
  $tableau_periode[$i] = CMbDT::transform('+' . $i . 'day', $debut_periode, '%Y-%m-%d');
}

$field_debut = "date_debut";
$field_fin   = "date_fin";
if ($type_view == "remplacement") {
  $field_debut = "debut";
  $field_fin   = "fin";
}
$where   = array();
$where[] = "(($field_debut >= '$debut_periode' AND $field_debut <= '$fin_periode')" .
  "OR ($field_fin >= '$debut_periode' AND $field_fin <= '$fin_periode')" .
  "OR ($field_debut <='$debut_periode' AND $field_fin >= '$fin_periode'))";

$prepare_in_user = CSQLDataSource::prepareIn(array_keys($mediusers), $filter->user_id);
if ($type_view == "conge") {
  $where["user_id"] = $prepare_in_user;
} else {
  $where[] = "remplace_id $prepare_in_user OR remplacant_id $prepare_in_user";
}

$orderby = $type_view == "conge" ? "user_id" : "remplace_id";

/** @var CPlageConge[]|CRemplacement[] $plages */
$plage  = $type_view == "conge" ? new CPlageConge() : new CRemplacement();
$plages = $plage->loadList($where, $orderby);
foreach ($plages as $_plage) {
  $_plage->_ref_user = $type_view == "conge" ? $_plage->loadRefUser() : $_plage->loadRefRemplace();
  $_plage->_ref_user->loadRefFunction();
  $_plage->_deb   = round(CMbDT::hoursRelative("$debut_periode 00:00:00", $_plage->$field_debut));
  $_plage->_fin   = round(CMbDT::hoursRelative($_plage->$field_debut, $_plage->$field_fin));
  $_plage->_duree = round(CMbDT::hoursRelative($_plage->$field_debut, $_plage->$field_fin));
}

$smarty = new CSmartyDP();

$smarty->assign("debut_periode", $debut_periode);
$smarty->assign("filter", $filter);
$smarty->assign("plages", $plages);
$smarty->assign("choix", $choix);
$smarty->assign("mediusers", $mediusers);
$smarty->assign("tableau_periode", $tableau_periode);
$smarty->assign("tab_start", $tab_start);
$smarty->assign("bank_holidays", $bank_holidays);
$smarty->assign("type_view", $type_view);
$smarty->assign("field_debut", $field_debut);
$smarty->assign("field_fin", $field_fin);
$smarty->assign("orderby", $orderby);

if (in_array($choix, array("semaine", "mois"))) {
  $smarty->display("inc_planning.tpl");
} else {
  $smarty->display("inc_planning_annee.tpl");
}
