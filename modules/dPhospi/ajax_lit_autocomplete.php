<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkRead();

$keywords   = CView::get("keywords", "str");
$service_id = CView::get("service_id", "ref class|CService");
$chambre_id = CView::get("chambre_id", "ref class|CChambre");
$group_id   = CView::get("group_id", "ref class|CGroups default|" . CGroups::loadCurrent()->_id);
$limit      = CView::get("limit", "num default|30");
$date_min   = CView::get("date_min", "dateTime");
$date_max   = CView::get("date_max", "dateTime");
$obstetrique = CView::get("obstetrique", "bool");

CView::enableSlave();
CView::checkin();

$lit = new CLit();

$ds = $lit->getDS();

$ljoin = [
  "chambre" => "chambre.chambre_id = lit.chambre_id",
  "service" => "service.service_id = chambre.service_id"
];

$where = [
  "lit.annule"        => "= '0'",
  "chambre.annule"    => "= '0'",
  "service.cancelled" => "= '0'",
  "service.group_id"  => $ds->prepare("= ?", $group_id)
];

if ($service_id) {
  $where["service.service_id"] = $ds->prepare("= ?", $service_id);
}
elseif ($obstetrique) {
  $services = CService::loadServicesObstetrique(false, $group_id);
  $where["service.service_id"] = CSQLDataSource::prepareIn(array_keys($services));
}

if ($chambre_id) {
  $where["chambre.chambre_id"] = "= '$chambre_id'";
}

$matches = $lit->getAutocompleteList($keywords, $where, $limit, $ljoin, "lit.nom");

foreach ($matches as $match) {
  $match->loadRefService();
}

// Seulement les lits dispos entre soit les deux dates passées en paramètre ou une seule date
if ($date_min || $date_max) {
  $ds = CSQLDataSource::get("std");

  $request = new CRequest();
  $request->addSelect("DISTINCT lit_id");
  $request->addTable("affectation");
  $request->addWhere(
    [
      "lit_id" => "IS NOT NULL"
    ]
  );

  if ($date_min && $date_max) {
    $request->addWhere(
      [
        "entree" => $ds->prepare("< ?", $date_max),
        "sortie" => $ds->prepare("> ?", $date_min),
      ]
    );
  }
  else {
    $request->addWhere(
      [
        "'$date_min' BETWEEN entree AND sortie"
      ]
    );
  }

  if ($service_id) {
    $request->addWhere(["service_id" => $ds->prepare("= ?", $service_id)]);
  }

  foreach ($ds->loadColumn($request->makeSelect()) as $lit_id) {
    if (!isset($matches[$lit_id])) {
      continue;
    }

    $matches[$lit_id]->_occupe = true;
  }
}

$smarty = new CSmartyDP();

$smarty->assign("matches", $matches);
$smarty->assign("f", "lit_id");
$smarty->assign("show_view", true);
$smarty->assign("nodebug", true);
$smarty->assign("template", null);

$smarty->display("inc_lit_autocomplete");
