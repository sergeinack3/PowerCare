<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CChambre;

CCanDo::checkRead();

$keywords   = CView::get("keywords", "str");
$service_id = CView::get("service_id", "ref class|CService");
$limit      = CView::get("limit", "num default|30");
$date_min   = CView::get("date_min", "dateTime");
$date_max   = CView::get("date_max", "dateTime");

CView::enableSlave();
CView::checkin();

$chambre = new CChambre();

$ljoin = array(
  "service" => "service.service_id = chambre.service_id"
);

$where = array(
  "chambre.annule"    => "= '0'",
  "service.cancelled" => "= '0'",
  "service.group_id"  => "= '" . CGroups::loadCurrent()->_id . "'"
);

if ($service_id) {
  $where["service.service_id"] = "= '$service_id'";
}

$matches = $chambre->getAutocompleteList($keywords, $where, $limit, $ljoin, "chambre.nom");

$smarty = new CSmartyDP();

$smarty->assign("matches", $matches);
$smarty->assign("f", "chambre_id");
$smarty->assign("show_view", true);
$smarty->assign("nodebug", true);
$smarty->assign("template", null);

$smarty->display("inc_chambre_autocomplete.tpl");