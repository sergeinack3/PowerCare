<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CItemPrestation;
use Ox\Mediboard\Hospi\CLit;

CCanDo::check();

$keywords = CValue::get("keywords", "%%");
$lit_id   = CValue::get("lit_id");

CView::enableSlave();

$lit = new CLit;
$lit->load($lit_id);

$liaisons_items = $lit->loadRefsLiaisonsItems();

$items_prestations     = CMbObject::massLoadFwdRef($liaisons_items, "item_prestation_id");
$items_prestations_ids = CMbArray::pluck($items_prestations, "object_id");

// Un niveau unique par prestation
$where                 = array();
$where["object_id"]    = CSQLDataSource::prepareNotIn($items_prestations_ids);
$where["object_class"] = " = 'CPrestationJournaliere'";
$where["group_id"]     = "= '" . CGroups::loadCurrent()->_id . "'";
$where["prestation_journaliere.actif"] = "= '1'";
$where["item_prestation.actif"]        = "= '1'";

$ljoin                           = array();
$ljoin["prestation_journaliere"] = "prestation_journaliere.prestation_journaliere_id = item_prestation.object_id";

$item_prestation = new CItemPrestation();

/** @var CItemPrestation[] $items_prestations */
$items_prestations = $item_prestation->seek($keywords, $where, null, null, $ljoin);

$items_by_prestation = array();
$prestations         = array();

foreach ($items_prestations as $_item_prestation) {
  if (!isset($items_by_prestation[$_item_prestation->object_id])) {
    $items_by_prestation[$_item_prestation->object_id] = array();
  }
  $items_by_prestation[$_item_prestation->object_id][$_item_prestation->rank] = $_item_prestation;

  if (!isset($prestations[$_item_prestation->object_id])) {
    $prestations[$_item_prestation->object_id] = $_item_prestation->loadRefObject();
  }
}

foreach ($items_by_prestation as &$_items) {
  ksort($_items);
}

$smarty = new CSmartyDP();

$smarty->assign("items_by_prestation", $items_by_prestation);
$smarty->assign("prestations", $prestations);

$smarty->display("inc_lit_liaison_item_autocomplete.tpl");
