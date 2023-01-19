<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Soins\CObjectifSoinCategorie;

CCanDo::checkRead();

$categorie      = new CObjectifSoinCategorie();
$categories     = array();
$orderCategorie = "libelle ASC";

$where      = "group_id = '" . CGroups::loadCurrent()->_id . "' OR group_id IS NULL";
$categories = $categorie->loadList($where, $orderCategorie);

foreach ($categories as $_categorie) {
  $_categorie->loadRefGroup();
}

$where_inactive = array(
  "group_id = '" . CGroups::loadCurrent()->_id . "' OR group_id IS NULL",
  "actif" => "= '0'",
);

$countInactive = $categorie->countList($where_inactive);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("categories", $categories);
$smarty->assign("countInactive", $countInactive);

$smarty->display("inc_vw_list_categories_objectif_soin");