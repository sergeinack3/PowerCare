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
use Ox\Core\CView;
use Ox\Mediboard\Labo\CCatalogueLabo;
use Ox\Mediboard\Mediusers\CFunctions;

CCanDo::checkEdit();

$catalogue_labo_id = CView::get("catalogue_labo_id", "ref class|CCatalogueLabo", true);

CView::checkin();

// Liste des fonctions disponibles
$functions = new CFunctions();
$functions = $functions->loadListWithPerms(PERM_EDIT, null, "text");

$catalogue = new CCatalogueLabo();

// Chargement de tous les catalogues
$where = array(
  "function_id" => "IS NULL OR function_id ".CSQLDataSource::prepareIn(array_keys($functions)),
  "pere_id"     => "IS NULL"
);

$listCatalogues = $catalogue->loadList($where, "identifiant");

foreach ($listCatalogues as &$_catalogue) {
  $_catalogue->loadRefsDeep();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("listCatalogues", $listCatalogues);
$smarty->assign("catalogue_id"  , $catalogue_labo_id);
$smarty->assign("edit"          , 1);

$smarty->display("inc_list_catalogues");
