<?php
/**
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Qualite\CEiItem;
use Ox\Mediboard\Qualite\CFicheEi;

CCanDo::checkRead();

$user = CUser::get();

$type                           = CValue::get("type");
$first                          = CValue::get("first");
$selected_user_id               = CValue::get("selected_user_id");
$selected_service_valid_user_id = CValue::get("selected_service_valid_user_id");
$elem_concerne                  = CValue::get("elem_concerne");
$evenements                     = CValue::get("evenements");
$filter_item                    = CValue::get("filter_item");

CValue::setSession("selected_user_id", $selected_user_id);
CValue::setSession("selected_service_valid_user_id", $selected_service_valid_user_id);
CValue::setSession("elem_concerne", $elem_concerne);
CValue::setSession("evenements", $evenements);
CValue::setSession("filter_item", $filter_item);

$selected_fiche_id = CValue::getOrSession("selected_fiche_id");

$where = array();
if ($elem_concerne) {
  $where["fiches_ei.elem_concerne"] = "= '$elem_concerne'";
}

if ($selected_user_id) {
  $where["fiches_ei.user_id"] = "= '$selected_user_id'";
}

if ($selected_service_valid_user_id) {
  $where["fiches_ei.service_valid_user_id"] = "= '$selected_service_valid_user_id'";
}

$user_id = null;
if ($type == "AUTHOR" || (CCanDo::edit() && !CCanDo::admin())) {
  $user_id = $user->_id;
}

if ($evenements) {
  $listeFiches           = CFicheEi::loadFichesEtat($type, $user_id, $where, 0, false, null, true);
  $item                  = new CEiItem;
  $item->ei_categorie_id = $evenements;
  $listTypes             = array_keys($item->loadMatchingList());

  foreach ($listeFiches as $id => $fiche) {
    if (count(array_intersect($fiche->_ref_evenement, $listTypes)) == 0) {
      unset($listeFiches[$id]);
    }
    if ($filter_item != "" && strrpos($fiche->evenements, $filter_item) === false) {
      unset($listeFiches[$id]);
    }
  }

  $countFiches = count($listeFiches);
  $listeFiches = array_slice($listeFiches, intval($first), 20, true); // PHP's LIMIT
}
else {
  $countFiches = CFicheEi::loadFichesEtat($type, $user_id, $where, 0, true);
  $listeFiches = CFicheEi::loadFichesEtat($type, $user_id, $where, 0, false, $countFiches > 20 ? $first : null);
}


// Création du template
$smarty = new CSmartyDP();

$smarty->assign("listeFiches", $listeFiches);
$smarty->assign("countFiches", $countFiches);
$smarty->assign("type", $type);
$smarty->assign("first", $first);
$smarty->assign("selected_fiche_id", $selected_fiche_id);

$smarty->display("inc_ei_liste.tpl");
