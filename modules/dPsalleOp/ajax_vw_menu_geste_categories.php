<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\SalleOp\CAnesthPeropCategorie;

CCanDo::checkRead();
$chapitre_id    = CView::get("chapitre_id", "ref class|CAnesthPeropChapitre");
$categorie_ids  = CView::get("categorie_ids", "str");
$see_all_gestes = CView::get("see_all_gestes", "bool default|0", true);
CView::checkin();

$where                = array();
$where["actif"]       = " = '1'";

if (($categorie_ids == "0" || $categorie_ids) && !$chapitre_id) {
  $categorie_ids = explode("|", $categorie_ids);
  CMbArray::removeValue("", $categorie_ids);

  $where["anesth_perop_categorie_id"] = CSQLDataSource::prepareIn($categorie_ids);
}
else {
  $where["chapitre_id"] = $chapitre_id ? " = '$chapitre_id'" : " IS NULL";
}

if (($chapitre_id || ($categorie_ids == "" && !$chapitre_id)) || (in_array("0", $categorie_ids))) {
  $categorie = new CAnesthPeropCategorie();
  $categorie->libelle = CAppUI::tr("common-No category");

  $geste_categories = array(CAppUI::tr("common-No category") => $categorie);
}

$categorie  = new CAnesthPeropCategorie();
$categories = $categorie->loadGroupList($where, "libelle ASC");

foreach ($categories as $_categorie) {
  $_categorie->loadRefsGestesPerop(array(), 1, $see_all_gestes);
  $geste_categories[$_categorie->_view] = $_categorie;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("categories"       , $geste_categories);
$smarty->assign("chapitre_selected", true);
$smarty->assign("see_all_gestes"   , $see_all_gestes);
$smarty->display("inc_vw_menu_geste_categories");
