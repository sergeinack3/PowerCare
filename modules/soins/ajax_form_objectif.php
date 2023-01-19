<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Soins\CObjectifSoin;
use Ox\Mediboard\Soins\CObjectifSoinCategorie;

$sejour_id        = CView::get("sejour_id", "ref class|CSejour");
$objectif_soin_id = CView::get("objectif_soin_id", "ref class|CObjectifSoin");
CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

$objectif_soin = new CObjectifSoin();
if ($objectif_soin->load($objectif_soin_id)) {
  $objectif_soin->loadRefsReevaluations();
}
else {
  $objectif_soin->sejour_id = $sejour_id;
  $objectif_soin->date      = "now";
  $objectif_soin->user_id   = CAppUI::$user->_id;
}

$categorie       = new CObjectifSoinCategorie();
$list_categories = $categorie->loadActiveList();
if ($objectif_soin->objectif_soin_categorie_id && !array_key_exists($objectif_soin->objectif_soin_categorie_id, $list_categories)) {
  array_push($list_categories, $categorie->load($objectif_soin->objectif_soin_categorie_id));
}

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign("objectif_soin", $objectif_soin);
$smarty->assign("categories_objectif_soin", $list_categories);
$smarty->display("inc_form_objectif");