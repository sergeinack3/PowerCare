<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Labo\CCatalogueLabo;

CCanDo::checkRead();

//Chargement de tous les catalogues
$catalogue = new CCatalogueLabo;
$where = array("pere_id" => "IS NULL");
$order = "identifiant";
$listCatalogues = $catalogue->loadList($where, $order);
foreach ($listCatalogues as $key => $curr_catalogue) {
  $listCatalogues[$key]->loadRefsDeep();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("listCatalogues", $listCatalogues);

$smarty->display("add_packs_exams");
