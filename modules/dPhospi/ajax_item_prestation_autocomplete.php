<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CItemPrestation;

CCanDo::check();

$keywords   = CValue::get("keywords");
$type_hospi = CValue::get("type_hospi");
$type_pec   = CValue::get("type_pec");

CView::enableSlave();

$item_prestation = new CItemPrestation();

$where = array();
$ljoin = array();

$ljoin["prestation_ponctuelle"] = "prestation_ponctuelle.prestation_ponctuelle_id = item_prestation.object_id";

$where["prestation_ponctuelle.group_id"] = "= '" . CGroups::loadCurrent()->_id . "'";
$where["prestation_ponctuelle.forfait"]  = "= '0'";
$where["prestation_ponctuelle.actif"]    = "= '1'";
$where["item_prestation.object_class"]   = "= 'CPrestationPonctuelle'";
$where["item_prestation.actif"]          = "= '1'";

if ($type_hospi) {
  $where[] = "prestation_ponctuelle.type_hospi IS NULL OR prestation_ponctuelle.type_hospi = '$type_hospi'";
}
if ($type_pec) {
  // On filtre sur les prestations sur le type de pec
  $where[] = "(prestation_ponctuelle.M = '0' AND prestation_ponctuelle.C = '0' AND prestation_ponctuelle.O = '0') OR prestation_ponctuelle.$type_pec = '1'";
}

$matches = $item_prestation->getAutocompleteList($keywords, $where, null, $ljoin);

$smarty = new CSmartyDP("modules/system");

$smarty->assign("matches", $matches);
$smarty->assign("view_field", "nom");
$smarty->assign("template", "");
$smarty->assign("show_view", "");
$smarty->assign("input", $keywords);

$smarty->display("inc_field_autocomplete.tpl");
