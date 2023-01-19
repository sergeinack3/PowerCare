<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Labo\CCatalogueLabo;
use Ox\Mediboard\Mediusers\CFunctions;

CCanDo::checkEdit();

// Liste des fonctions disponibles
$functions = new CFunctions();
$order = "text";
$functions = $functions->loadListWithPerms(PERM_EDIT, null, $order);

// Chargement de tous les catalogues
$catalogue = new CCatalogueLabo();
$where = array(
  "pere_id"     => "IS NULL",
  "function_id" => "IS NULL OR function_id ".CSQLDataSource::prepareIn(array_keys($functions))
);

$listCatalogues = $catalogue->loadList($where, "identifiant");
foreach ($listCatalogues as $_catalogue) {
  $_catalogue->loadRefsDeep();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("listCatalogues", $listCatalogues);

$smarty->display("vw_edit_examens");
